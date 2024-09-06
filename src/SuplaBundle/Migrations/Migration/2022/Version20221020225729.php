<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * 2.New supla_auto_gate_closing table
 * 3.New supla_v_auto_gate_closing view
 * 5.New supla_mark_gate_open procedure
 */
class Version20221020225729 extends NoWayBackMigration {
    public function migrate() {
        $this->createAutoGateClosingTable();
        $this->createAutoGateClosingView();
        $this->createMarkGateOpenProcedure();
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

    private function createAutoGateClosingView() {
        $view = <<<VIEW
CREATE OR REPLACE VIEW supla_v_auto_gate_closing AS SELECT
    `c`.`user_id` AS `user_id`,
    `c`.`enabled` AS `enabled`,
    `dc`.`iodevice_id` AS `device_id`,    
    `c`.`channel_id` AS `channel_id`,
    `supla_is_now_active`(
        `c`.`active_from`,
        `c`.`active_to`,
        `c`.`active_hours`,
        `u`.`timezone`
    ) AS `is_now_active`,
    `c`.`max_time_open` AS `max_time_open`,
    `c`.`seconds_open` AS `seconds_open`,
    `c`.`closing_attempt` AS `closing_attempt`,
    `c`.`last_seen_open` AS `last_seen_open`
FROM
    (
        `supla_auto_gate_closing` `c`
    JOIN `supla_user` `u`
    JOIN `supla_dev_channel` `dc`
    )
WHERE
    `c`.`user_id` = `u`.`id` AND `c`.`channel_id` = `dc`.`id`
VIEW;
        $this->addSql($view);
    }

    private function createMarkGateOpenProcedure() {
        $this->addSql('DROP PROCEDURE IF EXISTS supla_mark_gate_open');
        $view = <<<VIEW
CREATE PROCEDURE `supla_mark_gate_open`(IN `_channel_id` INT)
BEGIN
    -- We assume the server will mark open gates at least every minute.
    UPDATE
        `supla_auto_gate_closing`
    SET
        seconds_open = NULL,
        closing_attempt = NULL,
        last_seen_open = NULL
    WHERE
        channel_id = _channel_id AND last_seen_open IS NOT NULL AND TIMESTAMPDIFF(MINUTE, last_seen_open, UTC_TIMESTAMP()) >= 4;
        
    UPDATE
        `supla_auto_gate_closing`
    SET
        seconds_open = IFNULL(seconds_open, 0) + IFNULL(
            UNIX_TIMESTAMP(UTC_TIMESTAMP()) - UNIX_TIMESTAMP(last_seen_open),
            0),
            last_seen_open = UTC_TIMESTAMP()
        WHERE
            channel_id = _channel_id;
      
      SELECT
            max_time_open - seconds_open AS `seconds_left`
      FROM
            `supla_auto_gate_closing`
      WHERE
             channel_id = _channel_id;
END
VIEW;
        $this->addSql($view);
    }
}
