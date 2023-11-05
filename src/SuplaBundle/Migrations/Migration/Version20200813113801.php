<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * user_id field added to supla_dev_channel_value table
 * channel_id fk added
 */
class Version20200813113801 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_dev_channel_value ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE supla_dev_channel_value ADD CONSTRAINT FK_1B99E01472F5A1AA FOREIGN KEY (channel_id) REFERENCES supla_dev_channel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_dev_channel_value ADD CONSTRAINT FK_1B99E014A76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id)');
        $this->addSql('CREATE INDEX IDX_1B99E014A76ED395 ON supla_dev_channel_value (user_id)');

        $this->addSql('DROP PROCEDURE IF EXISTS `supla_update_channel_value`');

        $this->addSql(<<<PROCEDURE
CREATE PROCEDURE `supla_update_channel_value`(
    IN `_id` INT,
    IN `_user_id` INT,
    IN `_value` VARBINARY(8),
    IN `_validity_time_sec` INT
) NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER
BEGIN
    IF _validity_time_sec > 0 THEN
        SET @valid_to = DATE_ADD(UTC_TIMESTAMP(), INTERVAL _validity_time_sec SECOND);
        
        INSERT INTO `supla_dev_channel_value` (`channel_id`, `user_id`, `update_time`, `valid_to`, `value`) 
          VALUES(_id, _user_id, UTC_TIMESTAMP(), @valid_to, _value)
        ON DUPLICATE KEY UPDATE `value` = _value, update_time = UTC_TIMESTAMP(), valid_to = @valid_to;
         
    ELSE
        DELETE FROM `supla_dev_channel_value` WHERE `channel_id` = _id;
    END IF;
END
PROCEDURE
        );
    }
}
