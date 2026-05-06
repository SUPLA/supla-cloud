<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * New supla_auto_gate_closing table
 */
class Version20221020225729 extends NoWayBackMigration {
    public function migrate() {
        $this->createAutoGateClosingTable();
    }

    private function createAutoGateClosingTable() {
        $this->addSql('DROP TABLE IF EXISTS supla_auto_gate_closing');
        $table = <<<TABLE
CREATE TABLE `supla_auto_gate_closing`(
    `channel_id` INT(11) NOT NULL,
    `user_id` INT(11) NOT NULL,
    `enabled` TINYINT(1) DEFAULT '0' NOT NULL,
    `active_from` DATETIME DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
    `active_to` DATETIME DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
    `active_hours` varchar(768) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
    `max_time_open` int(11) NOT NULL,
    `seconds_open` int(11) DEFAULT NULL,
    `closing_attempt` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
    `last_seen_open` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
TABLE;
        $this->addSql($table);

        $this->addSql("ALTER TABLE `supla_auto_gate_closing` ADD PRIMARY KEY (`channel_id`)");
        $this->addSql('CREATE INDEX IDX_E176CB9FA76ED395 ON supla_auto_gate_closing (user_id)');
        $this->addSql('ALTER TABLE supla_auto_gate_closing ADD CONSTRAINT FK_E176CB9FA76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id)');
        $this->addSql('ALTER TABLE supla_auto_gate_closing ADD CONSTRAINT FK_E176CB9F72F5A1AA FOREIGN KEY (channel_id) REFERENCES supla_dev_channel (id) ON DELETE CASCADE');
    }
}
