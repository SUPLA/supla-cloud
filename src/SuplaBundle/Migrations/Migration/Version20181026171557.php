<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * New fields text_param2, text_param3 for supla_dev_channel,
 * New fields manufacturer_id, product_id for supal_iodevice,
 */
class Version20181026171557 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_dev_channel ADD text_param1 VARCHAR(255) DEFAULT NULL AFTER `param3`, ADD text_param2 VARCHAR(255) DEFAULT NULL AFTER `text_param1`, ADD text_param3 VARCHAR(255) DEFAULT NULL AFTER `text_param2`');
        $this->addSql('ALTER TABLE supla_iodevice ADD manufacturer_id smallint DEFAULT NULL, ADD product_id smallint DEFAULT NULL');
    }
}
