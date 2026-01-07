<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * 1. https://github.com/SUPLA/supla-core/issues/334
 */
class Version20200108201101 extends NoWayBackMigration {
    public function migrate() {
        $ic = ChannelType::IMPULSECOUNTER();
        $this->addSql("UPDATE `supla_dev_channel` SET param1 = param1 * 100 WHERE type=$ic AND param1 > 0");
    }
}
