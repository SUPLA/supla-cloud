<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Add reasonable ON DELETE CASCADE for relations with user.
 */
class Version20181024164957 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_dev_channel DROP FOREIGN KEY FK_81E928C9125F95D6');
        $this->addSql('ALTER TABLE supla_dev_channel ADD CONSTRAINT FK_81E928C9125F95D6 FOREIGN KEY (iodevice_id) REFERENCES supla_iodevice (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_oauth_clients DROP FOREIGN KEY FK_4035AD80A76ED395');
        $this->addSql('ALTER TABLE supla_oauth_clients ADD CONSTRAINT FK_4035AD80A76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_oauth_access_tokens DROP FOREIGN KEY FK_2402564B96D1E204');
        $this->addSql('ALTER TABLE supla_oauth_access_tokens DROP FOREIGN KEY FK_2402564BA76ED395');
        $this->addSql('ALTER TABLE supla_oauth_access_tokens ADD CONSTRAINT FK_2402564B96D1E204 FOREIGN KEY (access_id_id) REFERENCES supla_accessid (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_oauth_access_tokens ADD CONSTRAINT FK_2402564BA76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_audit DROP FOREIGN KEY FK_EFE348F4A76ED395');
        $this->addSql('ALTER TABLE supla_audit ADD CONSTRAINT FK_EFE348F4A76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE supla_oauth_refresh_tokens DROP FOREIGN KEY FK_B809538CA76ED395');
        $this->addSql('ALTER TABLE supla_oauth_refresh_tokens CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE supla_oauth_refresh_tokens ADD CONSTRAINT FK_B809538CA76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_oauth_auth_codes DROP FOREIGN KEY FK_48E00E5DA76ED395');
        $this->addSql('ALTER TABLE supla_oauth_auth_codes CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE supla_oauth_auth_codes ADD CONSTRAINT FK_48E00E5DA76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id) ON DELETE CASCADE');
    }
}
