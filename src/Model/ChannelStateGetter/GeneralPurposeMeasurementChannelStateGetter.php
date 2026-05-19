<?php
namespace App\Model\ChannelStateGetter;

use App\Entity\Main\IODeviceChannel;
use App\Enums\ChannelFunction;
use App\Supla\SuplaServerAware;

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
