<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * IODeviceChannel#location
 */
class Version20180203231115 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_dev_channel ADD location_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_dev_channel ADD CONSTRAINT FK_81E928C964D218E FOREIGN KEY (location_id) REFERENCES supla_location (id)');
        $this->addSql('CREATE INDEX IDX_81E928C964D218E ON supla_dev_channel (location_id)');
    }
}
