<?php
namespace SuplaApiBundle\Model;

use SuplaApiBundle\Exception\ApiException;
use Symfony\Component\HttpFoundation\Response;

class RemoteSuplaServer {
    /** @var string */
    private $address;
    private $local;

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

    public function resetPasswordToken(string $username, string $locale): array {
        return $this->postRequest('forgotten-password', [
            'email' => $username,
            'locale' => $locale,
        ]);
    }

    public function isLocal(): bool {
        return $this->local;
    }

    private function postRequest(string $apiEndpoint, array $data): array {
        $content = json_encode($data);
        $ch = curl_init($this->address . '/api/' . $apiEndpoint);
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
