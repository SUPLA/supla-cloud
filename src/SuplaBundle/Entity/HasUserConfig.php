<?php

namespace SuplaBundle\Entity;

interface HasUserConfig extends ActionableSubject {
    public function getProperties(): array;

    public function setUserConfig(array $config): void;

    public function setUserConfigValue(string $valueName, $value): void;

    public function getUserConfig(): array;

    public function getUserConfigValue(string $valueName, $default = null);
}
