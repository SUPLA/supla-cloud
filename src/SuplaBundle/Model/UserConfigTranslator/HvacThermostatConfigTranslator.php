<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assertion;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Enums\ChannelFunction;

/**
 * @OA\Schema(schema="ChannelConfigHvacThermostat", description="Config for HVAC Thermostat.",
 *   @OA\Property(property="subfunction", type="string", enum={"COOL", "HEAT"}),
 * )
 */
class HvacThermostatConfigTranslator implements UserConfigTranslator {
    use FixedRangeParamsTranslator;

    public function getConfig(HasUserConfig $subject): array {
        $subFunction = $subject->getUserConfigValue('subfunction');
        if ($subFunction) {
            return [
                'subfunction' => $subFunction,
            ];
        } else {
            return [
                'waitingForConfigInit' => true,
            ];
        }
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('subfunction', $config)) {
            if (!$config['subfunction']) {
                $subject->setUserConfig([]);
                return;
            } else {
                Assertion::inArray($config['subfunction'], ['COOL', 'HEAT']);
                $subject->setUserConfigValue('subfunction', $config['subfunction']);
            }
        }
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::HVAC_THERMOSTAT,
        ]);
    }
}
