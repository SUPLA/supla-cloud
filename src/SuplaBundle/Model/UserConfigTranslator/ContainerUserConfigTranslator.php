<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

/**
 * @OA\Schema(schema="ChannelConfigContainer", description="Config for `CONTAINER*` functions.",
 *   @OA\Property(property="sensors", type="boolean"),
 * )
 */
class ContainerUserConfigTranslator extends UserConfigTranslator {
    use FixedRangeParamsTranslator;

    public function getConfig(HasUserConfig $subject): array {
        return [
            'sensors' => $this->translateSensorsArray($subject),
        ];
    }

    public function setConfig(HasUserConfig $subject, array $config) {

    }

    private function translateSensorsArray(IODeviceChannel $subject): array {
        $sensorsDictionary = $subject->getUserConfigValue('sensors', []);
        $sensors = [];
        foreach ($sensorsDictionary as $sensorNo => $config) {
            $channel = $this->channelNoToChannel($subject, $sensorNo);
            if (is_int($config['fillLevel'] ?? null) && $channel) {
                $sensors[] = array_merge($config, ['channelId' => $channel->getId()]);
            }
        }
        usort($sensors, fn(array $sensorA, array $sensorB) => $sensorA['fillLevel'] - $sensorB['fillLevel']);
        return $sensors;
    }

    private function channelNoToChannel(IODeviceChannel $channel, int $channelNo): ?IODeviceChannel {
        $device = $channel->getIoDevice();
        return $device->getChannels()->filter(function (IODeviceChannel $ch) use ($channelNo) {
            return $ch->getChannelNumber() == $channelNo;
        })->first() ?: null;
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::CONTAINER,
            ChannelFunction::SEPTIC_TANK,
            ChannelFunction::WATER_TANK,
        ]);
    }
}
