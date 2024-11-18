<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assertion;
use SuplaBundle\Entity\HasUserConfig;

class HiddenReadOnlyConfigFieldsUserConfigTranslator extends UserConfigTranslator {
    use FixedRangeParamsTranslator;

    private array $idsToIgnore = [];

    public function getConfig(HasUserConfig $subject): array {
        return [
            'readOnlyConfigFields' => $this->addChannelIdsToChannelNoParametersNames($subject->getProperty('readOnlyConfigFields', [])),
            'hiddenConfigFields' => $this->addChannelIdsToChannelNoParametersNames($subject->getProperty('hiddenConfigFields', [])),
        ];
    }

    private function addChannelIdsToChannelNoParametersNames(array $fields) {
        foreach (array_filter($fields, fn($f) => str_ends_with($f, 'ChannelNo')) as $channelNoParameter) {
            $fields[] = substr($channelNoParameter, 0, -2) . 'Id';
        }
        return $fields;
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (!in_array($subject->getId(), $this->idsToIgnore)) {
            $currentConfig = $this->getConfig($subject);
            $roFields = array_values(array_unique(array_merge(
                $currentConfig['readOnlyConfigFields'],
                $currentConfig['hiddenConfigFields'],
            )));
            $changedRoFields = array_intersect_key($config, array_flip($roFields));
            Assertion::noContent(
                $changedRoFields,
                'You cannot change the following config keys: ' . implode(', ', array_keys($changedRoFields))
            );
        }
    }

    public function supports(HasUserConfig $subject): bool {
        return $subject->getProperty('readOnlyConfigFields') || $subject->getProperty('hiddenConfigFields');
    }

    public function setIdsToIgnore(array $idsToIgnore): void {
        $this->idsToIgnore = $idsToIgnore;
    }
}
