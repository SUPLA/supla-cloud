<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Add AccessID <-> AccessToken relationship.
 */
class Version20181019115859 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_oauth_access_tokens ADD access_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_oauth_access_tokens ADD CONSTRAINT FK_2402564B96D1E204 FOREIGN KEY (access_id_id) REFERENCES supla_accessid (id)');
        $this->addSql('CREATE INDEX IDX_2402564B96D1E204 ON supla_oauth_access_tokens (access_id_id)');
        $this->addSql('ALTER TABLE supla_user_icons DROP FOREIGN KEY FK_EEB07467A76ED395');
        $this->addSql('DROP INDEX idx_eeb07467a76ed395 ON supla_user_icons');
        $this->addSql('CREATE INDEX IDX_27B32ACA76ED395 ON supla_user_icons (user_id)');
        $this->addSql('ALTER TABLE supla_user_icons ADD CONSTRAINT FK_EEB07467A76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_oauth_clients ADD public_client_id VARCHAR(255) DEFAULT NULL, DROP is_public, ADD long_description LONGTEXT DEFAULT NULL, CHANGE description description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_em_log CHANGE phase3_fae phase3_fae BIGINT DEFAULT NULL, CHANGE phase3_rae phase3_rae BIGINT DEFAULT NULL, CHANGE phase3_fre phase3_fre BIGINT DEFAULT NULL, CHANGE phase3_rre phase3_rre BIGINT DEFAULT NULL');
    }
}
