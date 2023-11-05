<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Add supla_dev_channel_group.location_id field with index and foreign key
 */
class Version20180208145738 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_dev_channel_group ADD location_id INT NOT NULL');
        $this->addSql('ALTER TABLE supla_dev_channel_group ADD CONSTRAINT FK_6B2EFCE564D218E FOREIGN KEY (location_id) REFERENCES supla_location (id)');
        $this->addSql('CREATE INDEX IDX_6B2EFCE564D218E ON supla_dev_channel_group (location_id)');
    }
}
