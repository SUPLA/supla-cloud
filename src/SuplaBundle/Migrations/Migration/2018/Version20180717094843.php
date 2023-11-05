<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Add more information to the oauth clients.
 */
class Version20180717094843 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_oauth_clients ADD user_id INT DEFAULT NULL, ADD name VARCHAR(255) DEFAULT NULL, ADD description VARCHAR(255) DEFAULT NULL, ADD is_public TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE supla_oauth_clients ADD CONSTRAINT FK_4035AD80A76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id)');
        $this->addSql('CREATE INDEX IDX_4035AD80A76ED395 ON supla_oauth_clients (user_id)');
        $this->addSql('ALTER TABLE supla_oauth_access_tokens ADD name VARCHAR(100) DEFAULT NULL, CHANGE client_id client_id INT DEFAULT NULL');
    }
}
