<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Channel paramX indexes.
 */
class Version20210419201821 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('CREATE INDEX supla_dev_channel_param1_idx ON supla_dev_channel (param1)');
        $this->addSql('CREATE INDEX supla_dev_channel_param2_idx ON supla_dev_channel (param2)');
        $this->addSql('CREATE INDEX supla_dev_channel_param3_idx ON supla_dev_channel (param3)');
        $this->addSql('CREATE INDEX supla_dev_channel_param4_idx ON supla_dev_channel (param4)');
    }
}
