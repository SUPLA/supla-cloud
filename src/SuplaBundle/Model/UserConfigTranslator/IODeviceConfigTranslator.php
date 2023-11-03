<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assert;
use Assert\Assertion;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Utils\NumberUtils;

/**
 * @OA\Schema(schema="DeviceConfig", description="Configuration of the IO Device.",
 *   @OA\Property(property="statusLed", type="string", enum={"OFF_WHEN_CONNECTED", "ALWAYS_OFF", "ON_WHEN_CONNECTED"}),
 *   @OA\Property(property="screenBrightness", oneOf={
 *     @OA\Schema(type="integer", minimum=0, maximum=100),
 *     @OA\Schema(type="string", enum={"auto"})
 *   }),
 *   @OA\Property(property="buttonVolume", type="integer", minimum=0, maximum=100),
 *   @OA\Property(property="automaticTimeSync", type="boolean"),
 *   @OA\Property(property="homeScreen", type="object",
 *     @OA\Property(property="content", type="string", enum={"NONE", "TEMPERATURE", "HUMIDITY", "TIME", "TIME_DATE", "TEMPERATURE_TIME", "MAIN_AND_AUX_TEMPERATURE"}),
 *     @OA\Property(property="offDelay", type="integer", description="Number of seconds or `0` to disable."),
 *   ),
 *   @OA\Property(property="userInterface", type="object",
 *     @OA\Property(property="disabled", type="boolean"),
 *     @OA\Property(property="minAllowedTemperatureSetpointFromLocalUI", type="number"),
 *     @OA\Property(property="maxAllowedTemperatureSetpointFromLocalUI", type="number"),
 *   ),
 *   @OA\Property(property="userInterfaceConstraints", type="object",
 *     @OA\Property(property="minAllowedTemperatureSetpoint", type="number"),
 *     @OA\Property(property="maxAllowedTemperatureSetpoint", type="number"),
 *   ),
 *   @OA\Property(property="homeScreenContentAvailable", type="string", enum={"OFF", "TEMPERATURE", "HUMIDITY", "TIME", "TIME_DATE", "TEMPERATURE_TIME", "MAIN_AND_AUX_TEMPERATURE"}),
 * )
 */
class IODeviceConfigTranslator {
    /** @var HvacThermostatConfigTranslator */
    private $hvacConfigTranslator;

    public function __construct(HvacThermostatConfigTranslator $hvacConfigTranslator) {
        $this->hvacConfigTranslator = $hvacConfigTranslator;
    }

    public function getConfig(IODevice $device): array {
        $config = $device->getUserConfig();
        $properties = $device->getProperties();
        if ($properties['homeScreenContentAvailable'] ?? false) {
            $config['homeScreenContentAvailable'] = $properties['homeScreenContentAvailable'];
        }
        if ($config['userInterface'] ?? false) {
            $config['userInterfaceConstraints'] = $this->getUserInterfaceConstraints($device);
            if ($config['userInterface']['disabled'] === 'partial') {
                $config['userInterface']['minAllowedTemperatureSetpointFromLocalUI'] =
                    NumberUtils::maximumDecimalPrecision($config['userInterface']['minAllowedTemperatureSetpointFromLocalUI'] / 100);
                $config['userInterface']['maxAllowedTemperatureSetpointFromLocalUI'] =
                    NumberUtils::maximumDecimalPrecision($config['userInterface']['maxAllowedTemperatureSetpointFromLocalUI'] / 100);
            }
        }
        return $config;
    }

    public function setConfig(IODevice $device, array $config): void {
        $currentConfig = $device->getUserConfig();
        $config = array_diff_key($config, ['homeScreenContentAvailable' => '']);
        Assertion::allInArray(array_keys($config), array_keys($currentConfig));
        foreach ($config as $settingName => $value) {
            Assertion::keyExists($currentConfig, $settingName, 'Cannot set this setting in this device: ' . $settingName);
            if ($settingName === 'statusLed') {
                Assertion::inArray($value, ['OFF_WHEN_CONNECTED', 'ALWAYS_OFF', 'ON_WHEN_CONNECTED'], null, 'statusLed');
            }
            if ($settingName === 'screenBrightness') {
                if ($value !== 'auto') {
                    Assert::that($value, null, 'screenBrightness')->integer()->between(0, 100);
                }
            }
            if ($settingName === 'buttonVolume') {
                Assert::that($value, null, 'buttonVolume')->integer()->between(0, 100);
            }
            if ($settingName === 'userInterface') {
                Assert::that($value, null, 'userInterface')->isArray()->keyExists('disabled');
                Assertion::inArray($value['disabled'], [true, false, 'partial'], null, 'userInterface.disabled');
                if (is_bool($value['disabled'])) {
                    Assertion::count($value, 1, null, 'userInterface');
                } else {
                    $constraints = $this->getUserInterfaceConstraints($device);
                    $tempMin = $value['minAllowedTemperatureSetpointFromLocalUI'] ?? null;
                    if (!is_numeric($tempMin)) {
                        $tempMin = $constraints['minAllowedTemperatureSetpoint'];
                    }
                    $tempMax = $value['maxAllowedTemperatureSetpointFromLocalUI'] ?? null;
                    if (!is_numeric($tempMax)) {
                        $tempMax = $constraints['maxAllowedTemperatureSetpoint'];
                    }
                    Assertion::greaterOrEqualThan($tempMin, $constraints['minAllowedTemperatureSetpoint'], null, 'minTemp');
                    Assertion::lessOrEqualThan($tempMax, $constraints['maxAllowedTemperatureSetpoint'], null, 'maxTemp');
                    Assertion::lessThan($tempMin, $tempMax, 'Minimum temperature must be lower than maximum temperature.'); // i18n
                    $value = [
                        'disabled' => 'partial',
                        'minAllowedTemperatureSetpointFromLocalUI' => round($tempMin * 100),
                        'maxAllowedTemperatureSetpointFromLocalUI' => round($tempMax * 100),
                    ];
                }
            }
            if ($settingName === 'automaticTimeSync') {
                Assert::that($value, null, 'automaticTimeSync')->boolean();
            }
            if ($settingName === 'homeScreen') {
                Assert::that($value, null, 'homeScreen')->isArray()->keyExists('content')->keyExists('offDelay')->count(2);
                $availableModes = $device->getProperties()['homeScreenContentAvailable'] ?? [];
                Assertion::inArray($value['content'], $availableModes, null, 'homeScreen.content');
                Assert::that($value['offDelay'], null, 'homeScreen.offDelay')->integer()->between(0, 3600);
            }
            $device->setUserConfigValue($settingName, $value);
        }
    }

    private function getUserInterfaceConstraints(IODevice $device): array {
        $roomMins = [];
        $roomMaxs = [];
        foreach ($device->getChannels() as $channel) {
            if ($this->hvacConfigTranslator->supports($channel)) {
                $config = $this->hvacConfigTranslator->getConfig($channel);
                $constraints = $config['temperatureConstraints'] ?? [];
                if (isset($constraints['roomMin'])) {
                    $roomMins[] = $constraints['roomMin'];
                }
                if (isset($constraints['roomMax'])) {
                    $roomMaxs[] = $constraints['roomMax'];
                }
            }
        }
        return [
            'minAllowedTemperatureSetpoint' => $roomMins ? max($roomMins) : -1000,
            'maxAllowedTemperatureSetpoint' => $roomMaxs ? min($roomMaxs) : 1000,
        ];
    }
}
