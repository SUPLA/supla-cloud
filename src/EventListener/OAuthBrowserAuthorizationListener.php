<?php

namespace App\EventListener;

use App\Auth\AutodiscoverPublicClientStub;
use App\Auth\MappedApiClientResolver;
use App\Auth\SuplaOAuth2;
use App\Entity\Main\OAuth\ApiClient;
use App\Exception\ApiException;
use App\Supla\SuplaAutodiscover;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;
use OAuth2\OAuth2RedirectException;
use OAuth2\OAuth2ServerException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class OAuthBrowserAuthorizationListener implements EventSubscriberInterface {
    public function __construct(
        private readonly ClientManagerInterface $clientManager,
        private readonly MappedApiClientResolver $mappedClientResolver,
        private readonly SuplaAutodiscover $autodiscover,
        private readonly SuplaOAuth2 $oauth2Server,
        private readonly bool $browserSpaEnabled,
        private readonly string $projectDir,
    ) {
    }

    public static function getSubscribedEvents(): array {
        // After routing (32), before the security firewall (8).
        return [KernelEvents::REQUEST => ['onKernelRequest', 9]];
    }

    public function onKernelRequest(RequestEvent $event): void {
        $request = $event->getRequest();
        if (!$event->isMainRequest() || !$request->isMethod('GET') || $request->attributes->get('_route') !== 'fos_oauth_server_authorize') {
            return;
        }
        try {
            if ($this->hasDuplicateOAuthParameter($request->getQueryString() ?? '')) {
                throw new OAuth2ServerException((string) Response::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_REQUEST, 'Duplicate authorization parameters.');
            }
            $clientId = $request->query->all()['client_id'] ?? null;
            $client = is_string($clientId) && $clientId !== '' ? $this->resolveClient($clientId) : null;
            if (!$client) {
                throw new OAuth2ServerException((string) Response::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_CLIENT, 'Unknown OAuth application.');
            }
            $this->oauth2Server->validateBrowserAuthorizationRequest($request, $client);
            if ($this->browserSpaEnabled) {
                $spaIndex = $this->projectDir . '/public/index.html';
                if (!is_file($spaIndex) || !is_readable($spaIndex)) {
                    throw new \RuntimeException('The frontend application entry point is unavailable.');
                }
                $html = file_get_contents($spaIndex);
                if ($html === false) {
                    throw new \RuntimeException('The frontend application entry point is unavailable.');
                }
                $event->setResponse(new Response($html, Response::HTTP_OK, [
                    'Content-Type' => 'text/html; charset=UTF-8',
                    'Cache-Control' => 'no-store',
                ]));
            }
        } catch (OAuth2RedirectException $e) {
            $headers = $e->getResponseHeaders();
            $state = $request->query->get('state');
            if ($request->query->has('state') && !$state && isset($headers['Location'])) {
                $headers['Location'] .= '&state=' . rawurlencode((string) $state);
            }
            $event->setResponse(new Response('', Response::HTTP_FOUND, $headers));
        } catch (OAuth2ServerException $e) {
            $error = json_decode($e->getResponseBody(), true)['error'];
            throw new ApiException($error . ': ' . $e->getDescription(), Response::HTTP_BAD_REQUEST, $e);
        }
    }

    private function resolveClient(string $clientId): ?ApiClient {
        $client = $this->clientManager->findClientByPublicId($clientId);
        if (!$client instanceof ApiClient) {
            $client = null;
        }
        $data = null;
        if (!$client && $this->autodiscover->isTarget()) {
            try {
                // Public IDs can be validated from the public catalog directly;
                // only a non-catalog ID needs reverse mapped-client resolution.
                $data = $this->autodiscover->getPublicClient($clientId);
                $mappedPublicId = $data ? '' : $this->autodiscover->getPublicIdBasedOnMappedId($clientId);
            } catch (\Exception $exception) {
                throw new ApiException('Autodiscover is temporarily unavailable.', Response::HTTP_SERVICE_UNAVAILABLE, $exception);
            }
            if ($mappedPublicId) {
                $mappedClient = $this->mappedClientResolver->resolve($clientId);
                if ($mappedClient) {
                    return $mappedClient;
                }
            }
        }
        if (!$this->autodiscover->enabled()) {
            return $client;
        }
        if (!$data) {
            try {
                $publicId = $client ? $client->getPublicClientId() : $clientId;
                $data = $publicId ? $this->autodiscover->getPublicClient($publicId) : null;
                if (!$client && !$data && $this->autodiscover->isTarget()) {
                    $publicId = $this->autodiscover->getPublicIdBasedOnMappedId($clientId);
                    $data = $publicId ? $this->autodiscover->getPublicClient($publicId) : null;
                }
            } catch (\Exception $exception) {
                throw new ApiException('Autodiscover is temporarily unavailable.', Response::HTTP_SERVICE_UNAVAILABLE, $exception);
            }
        }
        if ($data) {
            // Validation must not provision clients or persist catalog updates.
            $client = $client ? clone $client : new AutodiscoverPublicClientStub($clientId);
            $client->updateDataFromAutodiscover($data);
        }
        return $client;
    }

    private function hasDuplicateOAuthParameter(string $query): bool {
        $oauthParameters = ['client_id', 'redirect_uri', 'response_type', 'scope', 'state', 'code_challenge', 'code_challenge_method'];
        $seen = [];
        foreach (explode('&', $query) as $parameter) {
            [$name] = explode('=', $parameter, 2);
            $name = urldecode($name);
            if (in_array($name, $oauthParameters, true)) {
                if (isset($seen[$name])) {
                    return true;
                }
                $seen[$name] = true;
            }
        }
        return false;
    }
}
