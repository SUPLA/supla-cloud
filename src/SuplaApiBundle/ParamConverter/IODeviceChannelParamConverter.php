<?php
namespace SuplaApiBundle\ParamConverter;

use SuplaBundle\Entity\IODeviceChannel;

class IODeviceChannelParamConverter extends AbstractBodyParamConverter {
    public function getConvertedClass(): string {
        return IODeviceChannel::class;
    }

    public function convert(array $requestData) {
        $channel = new IODeviceChannel();
        $function = $requestData['function'] ?? 0;
        if (is_array($function)) {
            $function = $function['id'] ?? 0;
        }
        $channel->setFunction($function);
        $channel->setParam1($requestData['param1'] ?? 0);
        $channel->setParam2($requestData['param2'] ?? 0);
        $channel->setParam3($requestData['param3'] ?? 0);
        return $channel;
    }
}
