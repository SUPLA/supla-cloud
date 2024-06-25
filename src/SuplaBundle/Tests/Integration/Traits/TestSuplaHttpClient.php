<?php
namespace SuplaBundle\Tests\Integration\Traits;

use Assert\Assertion;
use SuplaBundle\Supla\SuplaHttpClient;

class TestSuplaHttpClient extends SuplaHttpClient {
    public static $mockedResponses = [];

    public static function mockHttpRequest(string $url, $response) {
        Assertion::keyNotExists(self::$mockedResponses, $url, $url . ' is already mocked.');
        if (!is_string($response) && !is_callable($response)) {
            $response = json_encode($response);
        }
        self::$mockedResponses[$url] = $response;
    }

    public function request(string $fullUrl, string $method = null, ?array $payload = null, array $headers = []): array {
        foreach (self::$mockedResponses as $url => $response) {
            if (preg_match("#$url#i", $fullUrl)) {
                unset(self::$mockedResponses[$url]);
                if (is_callable($response)) {
                    return $response(['url' => $fullUrl, 'method' => $method, 'payload' => $payload, 'headers' => $headers]);
                } else {
                    return [true, $response, 200];
                }
            }
        }
        return parent::request($fullUrl, $method, $payload, $headers);
    }

    public static function reset() {
        self::$mockedResponses = [];
    }
}
