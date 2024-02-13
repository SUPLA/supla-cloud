<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaServerAware;

class GeneralPurposeMeasurementChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        $value = $this->suplaServer->getRawValue('GPM', $channel);
        $value = rtrim($value);
        $value = substr($value, strlen('VALUE:'));
        $value = is_numeric($value) ? floatval($value) : null;
        return ['value' => $value];
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::GENERAL_PURPOSE_METER(),
            ChannelFunction::GENERAL_PURPOSE_MEASUREMENT(),
        ];
    }
}
