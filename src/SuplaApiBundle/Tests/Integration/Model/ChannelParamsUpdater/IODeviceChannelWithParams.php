<?php
namespace SuplaApiBundle\Tests\Integration\Model\ChannelParamsUpdater;

use SuplaBundle\Entity\IODeviceChannel;

class IODeviceChannelWithParams extends IODeviceChannel {
    public function __construct(int $param1 = 0, int $param2 = 0, int $param3 = 0) {
        $this->setParam1($param1);
        $this->setParam2($param2);
        $this->setParam3($param3);
    }
}
