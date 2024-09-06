<?php

namespace SuplaBundle\Entity;

use Assert\Assertion;

/**
 * @property string $properties
 * @property string $userConfig
 */
trait HasUserConfigTrait {
    public function setUserConfig(array $config): void {
        $this->userConfig = $config ? json_encode($config, JSON_UNESCAPED_UNICODE) : '{}';
        Assertion::maxLength($this->userConfig, 8192, 'Value is too long for user_config field.');
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

    public function getProperty(string $valueName, $default = null) {
        return $this->getProperties()[$valueName] ?? $default;
    }
}
