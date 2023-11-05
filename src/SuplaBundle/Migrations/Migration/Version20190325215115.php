<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Double icon for HUMIDITYANDTEMPERATURE channel function.
 */
class Version20190325215115 extends NoWayBackMigration {
    public function migrate() {
        $functionId = ChannelFunction::HUMIDITYANDTEMPERATURE;
        $this->addSql("UPDATE supla_user_icons SET image2=image1 WHERE func = $functionId AND image2 IS NULL;");
    }
}
