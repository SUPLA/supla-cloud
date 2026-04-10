<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Add ON DELETE CASCADE to channel and channelGroup <-> DirectLink relation.
 */
class Version20181126225634 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_direct_link DROP FOREIGN KEY FK_6AE7809F72F5A1AA');
        $this->addSql('ALTER TABLE supla_direct_link DROP FOREIGN KEY FK_6AE7809F89E4AAEE');
        $this->addSql('ALTER TABLE supla_direct_link ADD CONSTRAINT FK_6AE7809F72F5A1AA FOREIGN KEY (channel_id) REFERENCES supla_dev_channel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_direct_link ADD CONSTRAINT FK_6AE7809F89E4AAEE FOREIGN KEY (channel_group_id) REFERENCES supla_dev_channel_group (id) ON DELETE CASCADE');
    }
}
