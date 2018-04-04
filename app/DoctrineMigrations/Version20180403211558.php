<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use SuplaBundle\Enums\AuditedAction;

/**
 * User login attempts table.
 */
class Version20180403211558 extends AbstractMigration {
    public function up(Schema $schema) {
        $authAction = AuditedAction::AUTHENTICATION;
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('CREATE TABLE supla_audit (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, action INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', successful TINYINT(1) DEFAULT \'1\' NOT NULL, ipv4 INT UNSIGNED DEFAULT NULL, text_param1 VARCHAR(255) DEFAULT NULL, text_param2 VARCHAR(255) DEFAULT NULL, INDEX IDX_EFE348F4A76ED395 (user_id), INDEX supla_audit_ipv4_idx (ipv4), INDEX supla_audit_created_at_idx (created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supla_audit ADD CONSTRAINT FK_EFE348F4A76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id)');
        $this->addSql("INSERT INTO supla_audit (user_id, text_param1, ipv4, created_at, `action`) SELECT id, email, last_ipv4, last_login, $authAction FROM supla_user WHERE last_login IS NOT NULL");
        $this->addSql("INSERT INTO supla_audit (user_id, text_param1, ipv4, created_at, `action`) SELECT id, email, current_ipv4, current_login, $authAction FROM supla_user WHERE current_login IS NOT NULL");
        $this->addSql('ALTER TABLE supla_user DROP last_login, DROP last_ipv4, DROP current_login, DROP current_ipv4');
    }

    public function down(Schema $schema) {
        $this->abortIf(true, 'There is no way back');
    }
}
