<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assert;
use Assert\Assertion;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Utils\NumberUtils;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

/**
 * @OA\Schema(schema="DeviceConfig", description="Configuration of the IO Device.",
 *   @OA\Property(property="statusLed", type="string"),
 *   @OA\Property(property="screenBrightness", type="object",
 *     @OA\Property(property="auto", type="boolean"),
 *     @OA\Property(property="level", type="integer", minimum=-100, maximum=100),
 *   ),
 *   @OA\Property(property="buttonVolume", type="integer", minimum=0, maximum=100),
 *   @OA\Property(property="automaticTimeSync", type="boolean"),
 *   @OA\Property(property="homeScreen", type="object",
 *     @OA\Property(property="content", type="string"),
 *     @OA\Property(property="offDelay", type="integer", description="Number of seconds or `0` to disable."),
 *     @OA\Property(property="offDelayType", type="string"),
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
 *   @OA\Property(property="homeScreenContentAvailable", type="string"),
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
        $props = $device->getProperties();
        if ($props['homeScreenContentAvailable'] ?? false) {
            $config['homeScreenContentAvailable'] = $props['homeScreenContentAvailable'];
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
        if ($config['screenBrightness'] ?? false) {
            $auto = $config['screenBrightness']['level'] === 'auto';
            $config['screenBrightness'] = [
                'auto' => $auto,
                'level' => $config['screenBrightness'][$auto ? 'adjustment' : 'level'],
            ];
        }
        if ($props['modbus'] ?? false) {
            $config['modbus'] = $config['modbus'] ?? [];
            if (!($config['modbus']['serial'] ?? false)) {
                $config['modbus']['serial']['mode'] = 'DISABLED';
            }
            if (!($config['modbus']['network'] ?? false)) {
                $config['modbus']['network']['mode'] = 'DISABLED';
            }
            $config['modbusConstraints'] = $this->getModbusConstraints($device);
        }
        return $config;
    }

    public function setConfig(IODevice $device, array $config): void {
        $currentConfig = $this->getConfig($device);
        $config = array_diff_key($config, ['homeScreenContentAvailable' => '']);
        Assertion::allInArray(array_keys($config), array_keys($currentConfig));
        foreach ($config as $settingName => $value) {
            Assertion::keyExists($currentConfig, $settingName, 'Cannot set this setting in this device: ' . $settingName);
            if ($settingName === 'statusLed') {
                Assertion::inArray($value, ['OFF_WHEN_CONNECTED', 'ALWAYS_OFF', 'ON_WHEN_CONNECTED'], null, 'statusLed');
            }
            if ($settingName === 'powerStatusLed') {
                Assertion::inArray($value, ['DISABLED', 'ENABLED'], null, 'statusLed');
            }
            if ($settingName === 'screenBrightness') {
                Assert::that($value, null, 'screenBrightness')->isArray()->keyIsset('level');
                $auto = $value['auto'] ?? false;
                if ($auto) {
                    Assert::that($value['level'], null, 'level')->integer()->between(-100, 100);
                    $value = ['level' => 'auto', 'adjustment' => $value['level']];
                } else {
                    Assert::that($value['level'], null, 'level')->integer()->between(1, 100);
                    $value = ['level' => $value['level']];
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
                $homeScreenConfig = [];
                Assert::that($value, null, 'homeScreen')->isArray();
                Assertion::allInArray(array_keys($value), ['content', 'offDelay', 'offDelayType'], null, 'homeScreen');
                $contentAvailable = $device->getProperties()['homeScreenContentAvailable'] ?? [];
                $hasOffDelay = ($currentConfig['homeScreen']['offDelay'] ?? false) !== false;
                $hasDelayType = $currentConfig['homeScreen']['offDelayType'] ?? false;
                if ($contentAvailable) {
                    Assert::that($value, null, 'homeScreen.content')->keyExists('content');
                    Assertion::inArray($value['content'], $contentAvailable, null, 'homeScreen.content');
                    $homeScreenConfig['content'] = $value['content'];
                }
                if ($hasOffDelay) {
                    Assert::that($value, null, 'homeScreen.offDelay')->keyExists('offDelay');
                    Assert::that($value['offDelay'], null, 'homeScreen.offDelay')->integer()->between(0, 3600);
                    $homeScreenConfig['offDelay'] = $value['offDelay'];
                }
                if ($hasDelayType) {
                    Assertion::keyExists($value, 'offDelayType', null, 'homeScreen.offDelayType');
                    Assertion::inArray($value['offDelayType'], ['ALWAYS_ENABLED', 'ENABLED_WHEN_DARK'], null, 'homeScreen.offDelayType');
                    $homeScreenConfig['offDelayType'] = $value['offDelayType'];
                }
                $value = $homeScreenConfig;
            }
            if ($settingName === 'modbus') {
                try {
                    $value = $this->buildModbusConfig($device, $value);
                } catch (InvalidConfigurationException $e) {
                    throw new \InvalidArgumentException('Invalid configuration for modbus: ' . $e->getMessage(), 400, $e);
                }
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

    private function buildModbusConfig(IODevice $device, array $config): array {
        $currentCfg = $this->getConfig($device)['modbus'] ?? [];
        $constraints = $this->getModbusConstraints($device);
        $configTree = new TreeBuilder('modbus');
        $configTree->getRootNode()->ignoreExtraKeys()->children()
            ->enumNode('role')->values(array_merge($constraints['availableRoles'], ['NOT_SET']))->defaultValue('NOT_SET')->end()
            ->integerNode('modbusAddress')->min(1)->max(247)->defaultValue(1)->end()
            ->integerNode('slaveTimeoutMs')->min(0)->max(10000)->defaultValue(0)->end()
            ->arrayNode('serial')->ignoreExtraKeys()->addDefaultsIfNotSet()->children()
            ->enumNode('mode')->values(array_merge($constraints['availableSerialModes'], ['DISABLED']))->defaultValue('DISABLED')->end()
            ->enumNode('baudrate')->values($constraints['availableSerialBaudrates'])->defaultValue($constraints['availableSerialBaudrates'][0])->end()
            ->enumNode('stopBits')->values($constraints['availableSerialStopbits'])->defaultValue($constraints['availableSerialStopbits'][0])->end()
            ->end()->end()
            ->arrayNode('network')->ignoreExtraKeys()->addDefaultsIfNotSet()->children()
            ->enumNode('mode')->values(array_merge($constraints['availableNetworkModes'], ['DISABLED']))->defaultValue('DISABLED')->end()
            ->integerNode('port')->min(0)->max(65535)->defaultValue(502)->end()
            ->end()->end()
            ->end();
        $processor = new Processor();
        $fullConfig = array_replace_recursive($currentCfg, $config);
        if (!$constraints['availableSerialModes']) {
            $fullConfig['serial'] = ['mode' => 'DISABLED'];
        }
        if (!$constraints['availableNetworkModes']) {
            $fullConfig['network'] = ['mode' => 'DISABLED'];
        }
        $newConfig = $processor->process($configTree->buildTree(), ['modbus' => $fullConfig]);
        return array_filter($newConfig, fn($v) => !is_array($v) || ($v['enabled'] ?? true));
    }

    private function getModbusConstraints(IODevice $device): array {
        $props = $device->getProperty('modbus', []);
        return [
            'availableRoles' => array_values(array_intersect($props['availableProtocols'] ?? [], ['MASTER', 'SLAVE'])),
            'availableSerialModes' => array_values(array_intersect($props['availableProtocols'] ?? [], ['RTU', 'ASCII'])),
            'availableNetworkModes' => array_values(array_intersect($props['availableProtocols'] ?? [], ['TCP', 'UDP'])),
            'availableSerialBaudrates' => $props['availableBaudrates'] ?? [],
            'availableSerialStopbits' => $props['availableStopbits'] ?? [],
        ];
    }
}
