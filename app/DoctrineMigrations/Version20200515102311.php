<?php

namespace Supla\Migrations;

/**
 * StateWebhook.
 */
class Version20200515102311 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('CREATE TABLE supla_state_webhooks (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, user_id INT DEFAULT NULL, url VARCHAR(255) NOT NULL, auth_token VARCHAR(255) NOT NULL, functions_ids VARCHAR(255) NOT NULL, enabled TINYINT(1) DEFAULT \'1\' NOT NULL, INDEX IDX_3C9361E019EB6921 (client_id), INDEX IDX_3C9361E0A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supla_state_webhooks ADD CONSTRAINT FK_3C9361E019EB6921 FOREIGN KEY (client_id) REFERENCES supla_oauth_clients (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_state_webhooks ADD CONSTRAINT FK_3C9361E0A76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id) ON DELETE CASCADE');
    }
}
