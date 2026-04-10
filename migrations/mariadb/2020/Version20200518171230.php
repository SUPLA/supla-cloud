<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * _fae_balanced and _rae_balanced fields added
 */
class Version20200518171230 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_em_log ADD fae_balanced BIGINT DEFAULT NULL, ADD rae_balanced BIGINT DEFAULT NULL');
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_add_em_log_item`');
        $this->addSql(<<<PROCEDURE
CREATE PROCEDURE `supla_add_em_log_item` (IN `_channel_id` INT(11), IN `_phase1_fae` BIGINT, IN `_phase1_rae` BIGINT, 
IN `_phase1_fre` BIGINT, IN `_phase1_rre` BIGINT, IN `_phase2_fae` BIGINT, IN `_phase2_rae` BIGINT, IN `_phase2_fre` BIGINT, 
IN `_phase2_rre` BIGINT, IN `_phase3_fae` BIGINT, IN `_phase3_rae` BIGINT, IN `_phase3_fre` BIGINT, IN `_phase3_rre` BIGINT,
IN `_fae_balanced` BIGINT, IN `_rae_balanced` BIGINT)  NO SQL
BEGIN

INSERT INTO `supla_em_log`(`channel_id`, `date`, `phase1_fae`, `phase1_rae`, `phase1_fre`, `phase1_rre`, `phase2_fae`, 
`phase2_rae`, `phase2_fre`, `phase2_rre`, `phase3_fae`, `phase3_rae`, `phase3_fre`, `phase3_rre`, `fae_balanced`, `rae_balanced`) 
VALUES (_channel_id, UTC_TIMESTAMP(), _phase1_fae, _phase1_rae, _phase1_fre, _phase1_rre, 
_phase2_fae, _phase2_rae, _phase2_fre, _phase2_rre, _phase3_fae, _phase3_rae, _phase3_fre, _phase3_rre,
_fae_balanced, _rae_balanced);

END
PROCEDURE
        );
    }
}
