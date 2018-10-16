<?php
namespace SuplaBundle\Model;

use SuplaBundle\Exception\ApiException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TargetSuplaCloud {
    /** @var string */
    private $address;
    private $local;

    /**
     * For the sake of tests.
     * @var callable
     */
    public static $requestExecutor;

    public function __construct(string $address, bool $local) {
        $this->address = $address;
        $this->local = $local;
    }

    public function issueWebappToken(string $username, string $password): array {
        return $this->postRequest('webapp-tokens', [
            'username' => $username,
            'password' => $password,
        ]);
    }

    public function issueOAuthToken(Request $request, array $mappedClientData): array {
        if ($request->getMethod() === 'POST') {
            $inputData = $request->request->all();
        } else {
            $inputData = $request->query->all();
        }
        $inputData = array_merge($inputData, $mappedClientData);
        return $this->postRequest('/oauth/v2/token', $inputData);
    }

    public function resetPasswordToken(string $username, string $locale): array {
        return $this->postRequest('forgotten-password', [
            'email' => $username,
            'locale' => $locale,
        ]);
    }

    public function registerUser(Request $request): array {
        return $this->postRequest('register', $request->request->all());
    }

    public function getOauthAuthUrl(array $oauthParams): string {
        return sprintf('%s/oauth/v2/auth?%s', $this->address, http_build_query($oauthParams));
    }

    public function getAddress(): string {
        return $this->address;
    }

    public function isLocal(): bool {
        return $this->local;
    }

    private function postRequest(string $apiEndpoint, array $data): array {
        if (self::$requestExecutor) {
            return (self::$requestExecutor)($apiEndpoint, $data);
        }
        $content = json_encode($data);
        if (strpos($apiEndpoint, '/') !== 0) {
            $apiEndpoint = '/api/' . $apiEndpoint;
        }
        $ch = curl_init($this->address . $apiEndpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Content-Length: ' . strlen($content)]);
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch) != 0) {
            throw new ApiException('Service temporarily unavailable.', Response::HTTP_SERVICE_UNAVAILABLE);
        }
        curl_close($ch);
        $response = json_decode($response, true);
        return [$response, $status];
    }
}
