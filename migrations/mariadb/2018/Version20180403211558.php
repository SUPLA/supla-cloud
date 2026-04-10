<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Enums\AuditedEvent;
use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * User login attempts table.
 */
class Version20180403211558 extends NoWayBackMigration {
    public function migrate() {
        $authEvent = AuditedEvent::AUTHENTICATION_SUCCESS;
        $this->addSql('CREATE TABLE supla_audit (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, event SMALLINT UNSIGNED NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', ipv4 INT UNSIGNED DEFAULT NULL, text_param VARCHAR(255) DEFAULT NULL, int_param INT DEFAULT NULL, INDEX IDX_EFE348F4A76ED395 (user_id), INDEX supla_audit_event_idx (event), INDEX supla_audit_ipv4_idx (ipv4), INDEX supla_audit_created_at_idx (created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supla_audit ADD CONSTRAINT FK_EFE348F4A76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id)');
        $this->addSql("INSERT INTO supla_audit (user_id, text_param, ipv4, created_at, event) SELECT id, email, last_ipv4, last_login, $authEvent FROM supla_user WHERE last_login IS NOT NULL");
        $this->addSql("INSERT INTO supla_audit (user_id, text_param, ipv4, created_at, event) SELECT id, email, current_ipv4, current_login, $authEvent FROM supla_user WHERE current_login IS NOT NULL");
        $this->addSql('ALTER TABLE supla_user DROP last_login, DROP last_ipv4, DROP current_login, DROP current_ipv4');
    }
}
