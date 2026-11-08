<?php

namespace App\EventListener;

use App\Auth\AutodiscoverPublicClientStub;
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
        private readonly SuplaAutodiscover $autodiscover,
        private readonly SuplaOAuth2 $oauth2Server,
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
            $clientId = $request->query->all()['client_id'] ?? null;
            $client = is_string($clientId) && $clientId !== '' ? $this->resolveClient($clientId) : null;
            if (!$client) {
                throw new OAuth2ServerException(Response::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_CLIENT, 'Unknown OAuth application.');
            }
            $this->oauth2Server->validateBrowserAuthorizationRequest($request, $client);
        } catch (OAuth2RedirectException $e) {
            $event->setResponse(new Response('', Response::HTTP_FOUND, $e->getResponseHeaders()));
        } catch (OAuth2ServerException $e) {
            $error = json_decode($e->getResponseBody(), true)['error'];
            throw new ApiException($error . ': ' . $e->getDescription(), Response::HTTP_BAD_REQUEST, $e);
        }
    }

    private function resolveClient(string $clientId): ?ApiClient {
        $client = $this->clientManager->findClientByPublicId($clientId);
        if (!$this->autodiscover->enabled()) {
            return $client;
        }
        $publicId = $client ? $client->getPublicClientId() : $clientId;
        $data = $publicId ? $this->autodiscover->getPublicClient($publicId) : null;
        if (!$client && !$data && $this->autodiscover->isTarget()) {
            $publicId = $this->autodiscover->getPublicIdBasedOnMappedId($clientId);
            $data = $publicId ? $this->autodiscover->getPublicClient($publicId) : null;
        }
        if ($data) {
            // Validation must not provision clients or persist catalog updates.
            $client = $client ? clone $client : new AutodiscoverPublicClientStub($clientId);
            $client->updateDataFromAutodiscover($data);
        }
        return $client;
    }
}
