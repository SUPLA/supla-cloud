<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use SuplaBundle\Entity\HasUserConfig;

abstract class UserConfigTranslator {
    abstract public function getConfig(HasUserConfig $subject): array;

    abstract public function setConfig(HasUserConfig $subject, array $config);

    public function clearConfig(HasUserConfig $subject): void {
        $config = $this->getConfig($subject);
        $config = array_map(function () {
            return null;
        }, $config);
        $this->setConfig($subject, $config);
    }

    abstract public function supports(HasUserConfig $subject): bool;
}
