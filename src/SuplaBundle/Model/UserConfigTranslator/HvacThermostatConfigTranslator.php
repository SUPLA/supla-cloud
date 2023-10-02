<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assert;
use Assert\Assertion;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Utils\NumberUtils;
use function Assert\Assert;

/**
 * @OA\Schema(schema="ChannelConfigHvacThermostat", description="Config for HVAC Thermostat.",
 *   @OA\Property(property="subfunction", type="string", enum={"COOL", "HEAT"}),
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
            $config = [
                'mainThermometerChannelId' => $mainThermometer->getId() === $subject->getId() ? null : $mainThermometer->getId(),
                'auxThermometerChannelId' => $auxThermometer ? $auxThermometer->getId() : null,
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
                $thermometer = $this->channelIdToNo($subject, $config['auxThermometerChannelId']);
                Assertion::inArray(
                    $thermometer->getFunction()->getId(),
                    [ChannelFunction::THERMOMETER, ChannelFunction::HUMIDITYANDTEMPERATURE]
                );
                Assertion::notEq($thermometer->getChannelNumber(), $subject->getUserConfigValue('mainThermometerChannelNo'));
                $subject->setUserConfigValue('auxThermometerChannelNo', $thermometer->getChannelNumber());
            } else {
                $subject->setUserConfigValue('auxThermometerChannelNo', null);
            }
        }
        if (array_key_exists('subfunction', $config) && $config['subfunction']) {
            Assertion::inArray($config['subfunction'], ['COOL', 'HEAT']);
            $subject->setUserConfigValue('subfunction', $config['subfunction']);
        }
        if (array_key_exists('weeklySchedule', $config) && $config['weeklySchedule']) {
            Assertion::isArray($subject->getUserConfigValue('weeklySchedule'));
            $availableProgramModes = $subject->getFunction()->getId() === ChannelFunction::HVAC_THERMOSTAT_AUTO
                ? ['HEAT', 'COOL', 'AUTO']
                : ['HEAT'];
            $weeklySchedule = $this->validateWeeklySchedule($config['weeklySchedule'], $availableProgramModes);
            $subject->setUserConfigValue('weeklySchedule', $weeklySchedule);
        }
        if (array_key_exists('altWeeklySchedule', $config) && $config['altWeeklySchedule']) {
            Assertion::isArray($subject->getUserConfigValue('altWeeklySchedule'));
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
                'quarters' => $week['quarters'],
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
            'quarters' => $weeklySchedule['quarters'],
        ];
    }
}
