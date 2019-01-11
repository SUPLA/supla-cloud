<?php
namespace SuplaBundle\Model;

class TargetSuplaCloud {
    /** @var string */
    private $address;
    private $local;

    public function __construct(string $address, bool $local = false) {
        $this->address = $address;
        $this->local = $local;
    }

    public function getOauthAuthUrl(array $oauthParams): string {
        return sprintf('%s/oauth/v2/auth?%s', $this->address, http_build_query($oauthParams));
    }

    public function getAddress(): string {
        return $this->address;
    }

    public function getProtocol(): string {
        return strtolower(parse_url($this->address, PHP_URL_SCHEME));
    }

    public function getHost($withPort = true): string {
        $port = parse_url($this->address, PHP_URL_PORT);
        return parse_url($this->address, PHP_URL_HOST) . ($port && $withPort ? ":$port" : '');
    }

    public function isLocal(): bool {
        return $this->local;
    }
}
