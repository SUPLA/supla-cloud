<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Support channel groups in schedules.
 */
class Version20180914222230 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_schedule ADD channel_group_id INT DEFAULT NULL, CHANGE channel_id channel_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_schedule ADD CONSTRAINT FK_323E8ABE89E4AAEE FOREIGN KEY (channel_group_id) REFERENCES supla_dev_channel_group (id)');
        $this->addSql('CREATE INDEX IDX_323E8ABE89E4AAEE ON supla_schedule (channel_group_id)');
    }
}
