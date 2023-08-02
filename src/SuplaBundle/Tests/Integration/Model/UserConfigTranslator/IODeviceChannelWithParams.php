<?php
namespace SuplaBundle\Tests\Integration\Model\UserConfigTranslator;

use SuplaBundle\Entity\Main\IODeviceChannel;

class IODeviceChannelWithParams extends IODeviceChannel {
    public function __construct(int $param1 = 0, int $param2 = 0, int $param3 = 0, int $param4 = 0) {
        $this->setParam1($param1);
        $this->setParam2($param2);
        $this->setParam3($param3);
        $this->setParam4($param4);
    }
}
