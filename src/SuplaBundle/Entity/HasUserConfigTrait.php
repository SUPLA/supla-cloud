<?php

namespace SuplaBundle\Entity;

/**
 * @property string $properties
 * @property string $userConfig
 */
trait HasUserConfigTrait {
    public function setUserConfig(array $config): void {
        $this->userConfig = $config ? json_encode($config, JSON_UNESCAPED_UNICODE) : '{}';
    }

    public function setUserConfigValue(string $valueName, $value): void {
        $config = $this->getUserConfig();
        $config[$valueName] = $value;
        $this->setUserConfig($config);
    }

    public function getUserConfig(): array {
        return $this->userConfig ? (json_decode($this->userConfig, true) ?: []) : [];
    }

    public function getUserConfigValue(string $valueName, $default = null) {
        return $this->getUserConfig()[$valueName] ?? $default;
    }
}
