<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Manufacturers update
 */
class Version20181105144611 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('UPDATE `supla_iodevice` SET manufacturer_id = 4 WHERE ifnull(manufacturer_id, 0) = 0 AND name LIKE \'ZAMEL %\'');
        $this->addSql('UPDATE `supla_iodevice` SET manufacturer_id = 5 WHERE ifnull(manufacturer_id, 0) = 0 AND name LIKE \'NICE %\'');
        $this->addSql('UPDATE `supla_iodevice` SET manufacturer_id = 6 WHERE ifnull(manufacturer_id, 0) = 0 AND name LIKE \'%sonoff%\'');
    }
}
