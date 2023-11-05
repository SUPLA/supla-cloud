<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Renaming the supla_channel_value table and giud column to supla_dev_channel_value, value
 * A new procedure named supla_update_channel_value has been added
 */
class Version20200811141801 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql("RENAME TABLE `supla_channel_value` TO `supla_dev_channel_value`");
        $this->addSql("ALTER TABLE `supla_dev_channel_value` CHANGE `guid` `value` VARBINARY(8) NOT NULL;");

        $this->addSql(<<<PROCEDURE
CREATE PROCEDURE `supla_update_channel_value`(
    IN `_id` INT,
    IN `_value` VARBINARY(8),
    IN `_validity_time_sec` INT
) NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER
BEGIN
    IF _validity_time_sec > 0 THEN
        SET @valid_to = DATE_ADD(UTC_TIMESTAMP(), INTERVAL _validity_time_sec SECOND);
        UPDATE `supla_dev_channel_value` SET `value` = _value,
            update_time = UTC_TIMESTAMP(),
            valid_to = @valid_to
        WHERE `channel_id` = _id;

        IF ROW_COUNT() = 0 THEN
           INSERT INTO `supla_dev_channel_value` (`channel_id`, `update_time`, `valid_to`, `value`) VALUES(_id, UTC_TIMESTAMP(), @valid_to, _value);
        END IF;
    ELSE
        DELETE FROM `supla_dev_channel_value` WHERE `channel_id` = _id;
    END IF;
END
PROCEDURE
        );
    }
}
