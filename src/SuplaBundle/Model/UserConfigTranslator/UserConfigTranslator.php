<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use SuplaBundle\Entity\HasUserConfig;

interface UserConfigTranslator {
    public function getConfig(HasUserConfig $subject): array;

    public function setConfig(HasUserConfig $subject, array $config);

    public function supports(HasUserConfig $subject): bool;
}
