<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Supla\SuplaServerAware;

/**
 * @see https://github.com/SUPLA/supla-core/issues/483#issuecomment-2445262873
 */
class ExtendedChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        $params = [
            $channel->getUser()->getId(),
            $channel->getIoDevice()->getId(),
            $channel->getId(),
            0, // sendRequest
            1, // getFromExtendedValue
        ];
        $result = $this->suplaServer->executeCommand('GET-CHANNEL-STATE:' . implode(',', $params));
        if (str_starts_with($result, 'STATE:')) {
            $values = explode(',', substr($result, strlen('STATE:')));
        } else {
            $values = [];
        }
        return [
            'ipv4Address' => $values[3] ?? null,
            'macAddress' => $values[4] ?? null,
            'batteryLevel' => intval($values[5] ?? 0),
            'isBatteryPowered' => boolval($values[6] ?? false),
        ];
    }

    public function supportedFunctions(): array {
        return [];
    }
}
