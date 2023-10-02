<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assert;
use Assert\Assertion;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Utils\NumberUtils;
use function Assert\Assert;

/**
 * @OA\Schema(schema="ChannelConfigHvacThermostatSchedule", description="Weekly schedule for thermostats.",
 *   @OA\Property(property="programSettings", type="array", @OA\Items(type="object",
 *     @OA\Property(property="mode", type="string", enum={"COOL", "HEAT", "AUTO"}),
 *     @OA\Property(property="setpointTemperatureHeat", type="float"),
 *     @OA\Property(property="setpointTemperatureCool", type="float"),
 *   )),
 *   @OA\Property(property="quarters", type="array", minLength=672, maxLength=672, @OA\Items(type="integer", enum={0,1,2,3,4}),
 *     description="Every item in the array represents the consequent quarter in the week and the chosen program. `0` is OFF. Starts with Monday."
 *   ),
 * )
 * @OA\Schema(schema="ChannelConfigHvacThermostat", description="Config for HVAC Thermostat.",
 *   @OA\Property(property="subfunction", type="string", enum={"COOL", "HEAT"}, description="Only for the `HVAC_THERMOSTAT` function."),
 *   @OA\Property(property="mainThermometerChannelId", type="integer"),
 *   @OA\Property(property="auxThermometerChannelId", type="integer"),
 *   @OA\Property(property="auxThermometerType", type="string", enum={"NOT_SET", "DISABLED", "FLOOR", "WATER", "GENERIC_HEATER", "GENERIC_COOLER"}),
 *   @OA\Property(property="antiFreezeAndOverheatProtectionEnabled", type="boolean"),
 *   @OA\Property(property="temperatureSetpointChangeSwitchesToManualMode", type="boolean"),
 *   @OA\Property(property="availableAlgorithms", type="array", readOnly=true, @OA\Items(type="string", enum={"ON_OFF_SETPOINT_MIDDLE", "ON_OFF_SETPOINT_AT_MOST"})),
 *   @OA\Property(property="usedAlgorithm", type="string", enum={"ON_OFF_SETPOINT_MIDDLE", "ON_OFF_SETPOINT_AT_MOST"}),
 *   @OA\Property(property="minOnTimeS", type="integer", minimum=0, maximum=600),
 *   @OA\Property(property="minOffTimeS", type="integer", minimum=0, maximum=600),
 *   @OA\Property(property="weeklySchedule", ref="#/components/schemas/ChannelConfigHvacThermostatSchedule"),
 *   @OA\Property(property="altWeeklySchedule", ref="#/components/schemas/ChannelConfigHvacThermostatSchedule", description="Only for the `HVAC_THERMOSTAT` function."),
 * )
 */
class HvacThermostatConfigTranslator implements UserConfigTranslator {
    use FixedRangeParamsTranslator;

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::HVAC_THERMOSTAT,
            ChannelFunction::HVAC_THERMOSTAT_AUTO,
            ChannelFunction::HVAC_THERMOSTAT_DIFFERENTIAL,
            ChannelFunction::HVAC_DOMESTIC_HOT_WATER,
        ]);
    }

    public function getConfig(HasUserConfig $subject): array {
        $mainThermometerChannelNo = $subject->getUserConfigValue('mainThermometerChannelNo');
        if (is_int($mainThermometerChannelNo) && $mainThermometerChannelNo >= 0) {
            $mainThermometer = $this->channelNoToId($subject, $mainThermometerChannelNo);
            $auxThermometerChannelNo = $subject->getUserConfigValue('auxThermometerChannelNo', -1);
            $auxThermometer = null;
            if ($auxThermometerChannelNo >= 0) {
                $auxThermometer = $this->channelNoToId($subject, $auxThermometerChannelNo);
            }
            $binarySensorChannelNo = $subject->getUserConfigValue('binarySensorChannelNo', -1);
            $binarySensor = null;
            if ($binarySensorChannelNo >= 0) {
                $binarySensor = $this->channelNoToId($subject, $binarySensorChannelNo);
            }
            $config = [
                'mainThermometerChannelId' => $mainThermometer->getId() === $subject->getId() ? null : $mainThermometer->getId(),
                'auxThermometerChannelId' => $auxThermometer ? $auxThermometer->getId() : null,
                'auxThermometerType' => $subject->getUserConfigValue('auxThermometerType', 'NOT_SET'),
                'binarySensorChannelId' => $binarySensor ? $binarySensor->getId() : null,
                'antiFreezeAndOverheatProtectionEnabled' => $subject->getUserConfigValue('antiFreezeAndOverheatProtectionEnabled', false),
                'temperatureSetpointChangeSwitchesToManualMode' =>
                    $subject->getUserConfigValue('temperatureSetpointChangeSwitchesToManualMode', false),
                'availableAlgorithms' => $subject->getProperties()['availableAlgorithms'] ?? [],
                'usedAlgorithm' => $subject->getUserConfigValue('usedAlgorithm'),
                'minOnTimeS' => $subject->getUserConfigValue('minOnTimeS', 0),
                'minOffTimeS' => $subject->getUserConfigValue('minOffTimeS', 0),
                'weeklySchedule' => $this->adjustWeeklySchedule($subject->getUserConfigValue('weeklySchedule')),
            ];
            if ($subject->getFunction()->getId() === ChannelFunction::HVAC_THERMOSTAT) {
                $config['subfunction'] = $subject->getUserConfigValue('subfunction');
                $config['altWeeklySchedule'] = $this->adjustWeeklySchedule($subject->getUserConfigValue('altWeeklySchedule'));
            }
            return $config;
        } else {
            return [
                'waitingForConfigInit' => true,
            ];
        }
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('mainThermometerChannelId', $config)) {
            if ($config['mainThermometerChannelId']) {
                Assertion::numeric($config['mainThermometerChannelId']);
                $thermometer = $this->channelIdToNo($subject, $config['mainThermometerChannelId']);
                Assertion::inArray(
                    $thermometer->getFunction()->getId(),
                    [ChannelFunction::THERMOMETER, ChannelFunction::HUMIDITYANDTEMPERATURE]
                );
                $subject->setUserConfigValue('mainThermometerChannelNo', $thermometer->getChannelNumber());
            } else {
                $subject->setUserConfigValue('mainThermometerChannelNo', $subject->getChannelNumber());
            }
        }
        if (array_key_exists('auxThermometerChannelId', $config)) {
            if ($config['auxThermometerChannelId']) {
                Assertion::numeric($config['auxThermometerChannelId']);
                $thermometer = $this->channelIdToNo($subject, $config['auxThermometerChannelId']);
                Assertion::inArray(
                    $thermometer->getFunction()->getId(),
                    [ChannelFunction::THERMOMETER, ChannelFunction::HUMIDITYANDTEMPERATURE]
                );
                Assertion::notEq($thermometer->getChannelNumber(), $subject->getUserConfigValue('mainThermometerChannelNo'));
                $subject->setUserConfigValue('auxThermometerChannelNo', $thermometer->getChannelNumber());
            } else {
                $subject->setUserConfigValue('auxThermometerChannelNo', null);
                $config['auxThermometerType'] = 'NOT_SET';
            }
        }
        if (array_key_exists('binarySensorChannelId', $config)) {
            if ($config['binarySensorChannelId']) {
                Assertion::numeric($config['binarySensorChannelId']);
                $sensor = $this->channelIdToNo($subject, $config['binarySensorChannelId']);
                Assertion::eq(ChannelType::SENSORNO, $sensor->getType()->getId(), 'Invalid sensor type.');
                Assertion::notEq(ChannelFunction::NONE, $sensor->getFunction()->getId(), 'Sensor function not chosen.');
                $subject->setUserConfigValue('binarySensorChannelNo', $sensor->getChannelNumber());
            } else {
                $subject->setUserConfigValue('binarySensorChannelId', null);
            }
        }
        if (array_key_exists('auxThermometerType', $config)) {
            if ($config['auxThermometerType']) {
                Assertion::inArray(
                    $config['auxThermometerType'],
                    ['NOT_SET', 'DISABLED', 'FLOOR', 'WATER', 'GENERIC_HEATER', 'GENERIC_COOLER']
                );
            }
            $subject->setUserConfigValue('auxThermometerType', $config['auxThermometerType'] ?: 'NOT_SET');
        }
        if (array_key_exists('antiFreezeAndOverheatProtectionEnabled', $config)) {
            $enabled = filter_var($config['antiFreezeAndOverheatProtectionEnabled'], FILTER_VALIDATE_BOOLEAN);
            $subject->setUserConfigValue('antiFreezeAndOverheatProtectionEnabled', $enabled);
        }
        if (array_key_exists('temperatureSetpointChangeSwitchesToManualMode', $config)) {
            $enabled = filter_var($config['temperatureSetpointChangeSwitchesToManualMode'], FILTER_VALIDATE_BOOLEAN);
            $subject->setUserConfigValue('temperatureSetpointChangeSwitchesToManualMode', $enabled);
        }
        if (array_key_exists('usedAlgorithm', $config) && $config['usedAlgorithm']) {
            $availableAlgorithms = $subject->getProperties()['availableAlgorithms'] ?? [];
            Assertion::inArray($config['usedAlgorithm'], $availableAlgorithms);
            $subject->setUserConfigValue('usedAlgorithm', $config['usedAlgorithm']);
        }
        if (array_key_exists('subfunction', $config) && $config['subfunction']) {
            Assertion::inArray($config['subfunction'], ['COOL', 'HEAT']);
            $subject->setUserConfigValue('subfunction', $config['subfunction']);
        }
        if (array_key_exists('minOnTimeS', $config)) {
            if ($config['minOnTimeS']) {
                Assert::that($config['minOnTimeS'])->numeric()->between(0, 600);
                $subject->setUserConfigValue('minOnTimeS', intval($config['minOnTimeS']));
            } else {
                $subject->setUserConfigValue('minOnTimeS', 0);
            }
        }
        if (array_key_exists('minOffTimeS', $config)) {
            if ($config['minOffTimeS']) {
                Assert::that($config['minOffTimeS'])->numeric()->between(0, 600);
                $subject->setUserConfigValue('minOffTimeS', intval($config['minOffTimeS']));
            } else {
                $subject->setUserConfigValue('minOffTimeS', 0);
            }
        }
        if (array_key_exists('weeklySchedule', $config) && $config['weeklySchedule']) {
            Assertion::isArray($subject->getUserConfigValue('weeklySchedule'));
            Assertion::isArray($config['weeklySchedule']);
            $availableProgramModes = $subject->getFunction()->getId() === ChannelFunction::HVAC_THERMOSTAT_AUTO
                ? ['HEAT', 'COOL', 'AUTO']
                : ['HEAT'];
            $weeklySchedule = $this->validateWeeklySchedule($config['weeklySchedule'], $availableProgramModes);
            $subject->setUserConfigValue('weeklySchedule', $weeklySchedule);
        }
        if (array_key_exists('altWeeklySchedule', $config) && $config['altWeeklySchedule']) {
            Assertion::isArray($subject->getUserConfigValue('altWeeklySchedule'));
            Assertion::isArray($config['altWeeklySchedule']);
            $weeklySchedule = $this->validateWeeklySchedule($config['altWeeklySchedule'], ['COOL']);
            $subject->setUserConfigValue('altWeeklySchedule', $weeklySchedule);
        }
    }

    private function channelNoToId(IODeviceChannel $channel, int $channelNo): IODeviceChannel {
        $device = $channel->getIoDevice();
        $channelWithNo = $device->getChannels()->filter(function (IODeviceChannel $ch) use ($channelNo) {
            return $ch->getChannelNumber() == $channelNo;
        })->first();
        Assertion::isObject($channelWithNo, 'Invalid channel number given: ' . $channelNo);
        return $channelWithNo;
    }

    private function channelIdToNo(IODeviceChannel $channel, int $channelId): IODeviceChannel {
        $device = $channel->getIoDevice();
        $channelWithId = $device->getChannels()->filter(function (IODeviceChannel $ch) use ($channelId) {
            return $ch->getId() == $channelId;
        })->first();
        Assertion::isObject($channelWithId, 'Invalid channel ID given: ' . $channelId);
        return $channelWithId;
    }

    private function adjustWeeklySchedule(?array $week): ?array {
        if ($week) {
            $quartersGoToChurch = array_merge(
                array_slice($week['quarters'], 24 * 4, 24 * 4), // Monday
                array_slice($week['quarters'], 2 * 24 * 4), // Tuesday - Saturday
                array_slice($week['quarters'], 0, 24 * 4) // Sunday
            );
            return [
                'programSettings' => array_map(function (array $programSettings) {
                    $min = in_array($programSettings['mode'], ['HEAT', 'AUTO'])
                        ? NumberUtils::maximumDecimalPrecision($programSettings['setpointTemperatureHeat'] / 100)
                        : null;
                    $max = in_array($programSettings['mode'], ['COOL', 'AUTO'])
                        ? NumberUtils::maximumDecimalPrecision($programSettings['setpointTemperatureCool'] / 100)
                        : null;
                    return ['mode' => $programSettings['mode'], 'setpointTemperatureHeat' => $min, 'setpointTemperatureCool' => $max];
                }, $week['programSettings']),
                'quarters' => $quartersGoToChurch,
            ];
        } else {
            return null;
        }
    }

    private function validateWeeklySchedule(array $weeklySchedule, array $availableProgramModes): array {
        Assert::that($weeklySchedule)->isArray()->notEmptyKey('quarters')->notEmptyKey('programSettings');
        Assert::that($weeklySchedule['programSettings'])
            ->isArray()
            ->all()
            ->isArray()
            ->keyExists('mode', 'setpointTemperatureHeat', 'setpointTemperatureCool');
        $availablePrograms = array_merge([0], array_keys($weeklySchedule['programSettings']));
        Assert::that($weeklySchedule['quarters'])
            ->isArray()
            ->count(24 * 7 * 4)
            ->all()
            ->inArray($availablePrograms);
        $quartersGoToChurch = array_merge(
            array_slice($weeklySchedule['quarters'], 6 * 24 * 4), // Sunday
            array_slice($weeklySchedule['quarters'], 0, 6 * 24 * 4) // Monday - Saturday
        );
        return [
            'programSettings' => array_map(function (array $programSettings) use ($availableProgramModes) {
                $programMode = $programSettings['mode'];
                Assertion::inArray($programMode, $availableProgramModes);
                $min = 0;
                $max = 0;
                if (in_array($programMode, ['HEAT', 'AUTO'])) {
                    $min = round($programSettings['setpointTemperatureHeat'] * 100);
                }
                if (in_array($programMode, ['COOL', 'AUTO'])) {
                    $max = round($programSettings['setpointTemperatureCool'] * 100);
                }
                if ($programMode === 'AUTO') {
                    Assertion::lessThan($min, $max);
                }
                return ['mode' => $programMode, 'setpointTemperatureHeat' => $min, 'setpointTemperatureCool' => $max];
            }, $weeklySchedule['programSettings']),
            'quarters' => $quartersGoToChurch,
        ];
    }
}
