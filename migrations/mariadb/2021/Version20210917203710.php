<?php

namespace SuplaBundle\Migrations\Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Two new procedures to update channel data
 */
class Version20210917203710 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql(<<<PROCEDURE
CREATE PROCEDURE `supla_update_channel_properties`(
    IN `_id` INT,
    IN `_user_id` INT,
    IN `_properties` VARCHAR(2048)
)
    NO SQL
BEGIN
    UPDATE supla_dev_channel SET properties = _properties WHERE id = _id AND user_id = _user_id;
END
PROCEDURE
        );

        $this->addSql(<<<PROCEDURE
CREATE PROCEDURE `supla_update_channel_params`(
    IN `_id` INT,
    IN `_user_id` INT,
    IN `_param1` INT,
    IN `_param2` INT,
    IN `_param3` INT,
    IN `_param4` INT
) NO SQL
BEGIN
    UPDATE
        supla_dev_channel
    SET
        param1 = _param1,
        param2 = _param2,
        param3 = _param3,
        param4 = _param4
    WHERE
        id = _id AND user_id = _user_id ; 
END
PROCEDURE
        );
    }
}
