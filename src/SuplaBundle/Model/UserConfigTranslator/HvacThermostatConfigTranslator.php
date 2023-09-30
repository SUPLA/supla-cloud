<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assertion;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

/**
 * @OA\Schema(schema="ChannelConfigHvacThermostat", description="Config for HVAC Thermostat.",
 *   @OA\Property(property="subfunction", type="string", enum={"COOL", "HEAT"}),
 * )
 */
class HvacThermostatConfigTranslator implements UserConfigTranslator {
    use FixedRangeParamsTranslator;

    public function getConfig(HasUserConfig $subject): array {
        $mainThermometerChannelNo = $subject->getUserConfigValue('mainThermometerChannelNo');
        if (is_int($mainThermometerChannelNo) && $mainThermometerChannelNo >= 0) {
            $mainThermometer = $this->channelNoToId($subject, $mainThermometerChannelNo);
            $auxThermometerChannelNo = $subject->getUserConfigValue('auxThermometerChannelNo', -1);
            $auxThermometer = null;
            if ($auxThermometerChannelNo >= 0) {
                $auxThermometer = $this->channelNoToId($subject, $auxThermometerChannelNo);
            }
            return [
                'subfunction' => $subject->getUserConfigValue('subfunction'),
                'mainThermometerChannelId' => $mainThermometer->getId() === $subject->getId() ? null : $mainThermometer->getId(),
                'auxThermometerChannelId' => $auxThermometer ? $auxThermometer->getId() : null,
            ];
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
        if (array_key_exists('subfunction', $config)) {
            if ($config['subfunction']) {
                Assertion::inArray($config['subfunction'], ['COOL', 'HEAT']);
                $subject->setUserConfigValue('subfunction', $config['subfunction']);
            } else {
                $subject->setUserConfigValue('subfunction', null);
            }
        }
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::HVAC_THERMOSTAT,
        ]);
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
}
