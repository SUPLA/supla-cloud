<?php

namespace App\Controller\OAuth;

use App\Exception\ApiException;
use App\Model\TargetSuplaCloud;
use App\Model\TargetSuplaCloudRequestForwarder;
use App\Supla\SuplaAutodiscover;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/** Creates a safe, explicit browser handoff to a registered private Cloud. */
class PrivateOAuthCloudController {
    private const AUTHORIZATION_FIELDS = ['client_id', 'redirect_uri', 'response_type', 'scope', 'state', 'code_challenge', 'code_challenge_method'];

    public function __construct(
        private readonly SuplaAutodiscover $autodiscover,
        private readonly TargetSuplaCloudRequestForwarder $requestForwarder,
    ) {
    }

    /** @Route("/api/oauth/private-cloud", methods={"POST"}) */
    public function selectAction(Request $request): JsonResponse {
        $data = $request->request->all();
        $allowedFields = array_merge(['targetCloud'], self::AUTHORIZATION_FIELDS);
        foreach ($data as $name => $value) {
            if (!in_array($name, $allowedFields, true) || !is_string($value)) {
                return $this->error('invalid_request', Response::HTTP_BAD_REQUEST);
            }
        }
        foreach (['targetCloud', 'client_id', 'redirect_uri', 'response_type', 'scope'] as $required) {
            if (!isset($data[$required]) || $data[$required] === '') {
                return $this->error('invalid_request', Response::HTTP_BAD_REQUEST);
            }
        }
        if (!$this->autodiscover->enabled() || !$this->autodiscover->isBroker()) {
            return $this->error('target_unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
        }

        try {
            $target = $this->privateTarget($data['targetCloud']);
            $mappedClientId = $this->autodiscover->getTargetCloudClientId($target, $data['client_id']);
        } catch (\InvalidArgumentException $e) {
            return $this->error('invalid_target', Response::HTTP_BAD_REQUEST);
        } catch (ApiException $e) {
            return $this->error('target_unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
        }
        if (!$mappedClientId) {
            return $this->error('invalid_client', Response::HTTP_BAD_REQUEST);
        }

        try {
            // The AD/private-cloud mapping is the trust lookup. Only after it
            // succeeds may the broker probe the host through its guarded peer
            // destination selector.
            $trustedTarget = TargetSuplaCloud::trustedPeer($target->getAddress());
            if (!$this->requestForwarder->getInfo($trustedTarget)) {
                return $this->error('target_unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
            }
        } catch (ApiException | \InvalidArgumentException $e) {
            return $this->error('target_unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $authorization = array_intersect_key($data, array_flip(self::AUTHORIZATION_FIELDS));
        $authorization['client_id'] = $mappedClientId;
        return new JsonResponse([
            'redirectUrl' => $trustedTarget->getOauthAuthUrl($authorization),
        ], Response::HTTP_OK, ['Cache-Control' => 'no-store']);
    }

    private function privateTarget(string $host): TargetSuplaCloud {
        if (strpbrk($host, '/\\?#@') !== false || preg_match('/\s|:\/\//', $host)) {
            throw new \InvalidArgumentException('Invalid private Cloud host.');
        }
        $parts = parse_url('https://' . $host);
        $hostname = $parts['host'] ?? '';
        if (
            !$hostname
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])
            || (isset($parts['path']) && $parts['path'] !== '')
            || (isset($parts['port']) && $parts['port'] < 1)
            || (!filter_var($hostname, FILTER_VALIDATE_IP) && !filter_var($hostname, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME))
        ) {
            throw new \InvalidArgumentException('Invalid private Cloud host.');
        }
        return TargetSuplaCloud::forHost('https', $host);
    }

    private function error(string $code, int $status): JsonResponse {
        return new JsonResponse(['outcome' => 'error', 'errorCode' => $code], $status, ['Cache-Control' => 'no-store']);
    }
}
