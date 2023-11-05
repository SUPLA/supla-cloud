<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * New field and procedure, for demo server purposes
 */
class Version20180423121539 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_client ADD disable_after_date DATETIME COMMENT \'(DC2Type:utcdatetime)\'');

        $this->addSql(<<<CREATE_PROCEDURE
			CREATE PROCEDURE `supla_on_newclient`(IN `_client_id` INT)
			    NO SQL
			BEGIN	
			END;
CREATE_PROCEDURE
        );
    }
}
