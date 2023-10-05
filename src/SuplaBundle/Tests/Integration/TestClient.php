<?php
namespace SuplaBundle\Tests\Integration;

use SuplaBundle\Model\ApiVersions;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class TestClient extends KernelBrowser {
    public function apiRequest(
        string $method,
        string $uri,
        $content = [],
        array $params = [],
        array $files = [],
        array $server = [],
        string $version = null
    ) {
        if (is_array($content)) {
            $content = json_encode($content);
        }
        $server['HTTP_X-Requested-With'] = 'XMLHttpRequest';
        $server['ACCEPT'] = 'application/json';
        $server['HTTP_Accept'] = 'application/json';
        $server['CONTENT_TYPE'] = 'application/json';
        if ($version !== null) {
            $server['HTTP_X_ACCEPT_VERSION'] = $version;
        }

        return $this->request($method, $uri, $params, $files, $server, $content);
    }

    public function apiRequestV22(string $method, string $uri, $content = [], array $params = [], array $files = [], array $server = []) {
        return $this->apiRequest($method, $uri, $content, $params, $files, $server, ApiVersions::V2_2);
    }

    public function apiRequestV23(string $method, string $uri, $content = [], array $params = [], array $files = [], array $server = []) {
        return $this->apiRequest($method, $uri, $content, $params, $files, $server, ApiVersions::V2_3);
    }

    public function apiRequestV24(string $method, string $uri, $content = [], array $params = [], array $files = [], array $server = []) {
        return $this->apiRequest($method, $uri, $content, $params, $files, $server, ApiVersions::V2_4);
    }

    public function apiRequestV3(string $method, string $uri, $content = [], array $params = [], array $files = [], array $server = []) {
        return $this->apiRequest($method, $uri, $content, $params, $files, $server, ApiVersions::V3);
    }

    public function getResponseBody(): array {
        return json_decode($this->getResponse()->getContent(), true);
    }
}
