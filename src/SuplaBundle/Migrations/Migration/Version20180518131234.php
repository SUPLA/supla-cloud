<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * The transition to correct OAuth 2.0 support.
 */
class Version20180518131234 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('DELETE FROM `supla_oauth_access_tokens`');
        $this->addSql('DELETE FROM `supla_oauth_refresh_tokens`');
        $this->addSql('ALTER TABLE supla_user ADD oauth_compat_username VARCHAR(64) DEFAULT NULL COMMENT \'For backward compatibility purpose\', ADD oauth_compat_password VARCHAR(64) DEFAULT NULL COMMENT \'For backward compatibility purpose\'');
        $this->addSql('UPDATE supla_user u JOIN supla_oauth_user oa ON oa.parent_id = u.id SET u.oauth_compat_username = CONCAT(\'api_\', oa.id), u.oauth_compat_password = oa.password WHERE oa.enabled = 1');
        $this->addSql('ALTER TABLE supla_oauth_access_tokens DROP FOREIGN KEY FK_2402564BA76ED395');
        $this->addSql('ALTER TABLE supla_oauth_auth_codes DROP FOREIGN KEY FK_48E00E5DA76ED395');
        $this->addSql('ALTER TABLE supla_oauth_refresh_tokens DROP FOREIGN KEY FK_B809538CA76ED395');
        $this->addSql('DROP TABLE supla_oauth_user');
        $this->addSql('ALTER TABLE supla_oauth_refresh_tokens ADD CONSTRAINT FK_B809538CA76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id)');
        $this->addSql('ALTER TABLE supla_oauth_auth_codes ADD CONSTRAINT FK_48E00E5DA76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id)');
        $this->addSql('ALTER TABLE supla_oauth_access_tokens ADD CONSTRAINT FK_2402564BA76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id)');
        $this->addSql('ALTER TABLE supla_oauth_clients DROP FOREIGN KEY FK_4035AD80727ACA70');
        $this->addSql('DROP INDEX IDX_4035AD80727ACA70 ON supla_oauth_clients');
        $this->addSql('ALTER TABLE supla_oauth_clients DROP parent_id');
    }
}
