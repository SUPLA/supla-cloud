<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * OAuth mqtt_broker_auth_password.
 * api_client_authorization_id field in AccessToken and RefreshToken tables.
 */
class Version20210118124714 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_oauth_client_authorizations ADD mqtt_broker_auth_password VARCHAR(128) DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_oauth_access_tokens ADD api_client_authorization_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_oauth_access_tokens ADD CONSTRAINT FK_2402564BCA22CF77 FOREIGN KEY (api_client_authorization_id) REFERENCES supla_oauth_client_authorizations (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_2402564BCA22CF77 ON supla_oauth_access_tokens (api_client_authorization_id)');
        $this->addSql('ALTER TABLE supla_oauth_refresh_tokens ADD api_client_authorization_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_oauth_refresh_tokens ADD CONSTRAINT FK_B809538CCA22CF77 FOREIGN KEY (api_client_authorization_id) REFERENCES supla_oauth_client_authorizations (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_B809538CCA22CF77 ON supla_oauth_refresh_tokens (api_client_authorization_id)');
    }
}
