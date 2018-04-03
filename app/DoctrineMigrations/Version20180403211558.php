<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * User login attempts table.
 */
class Version20180403211558 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('CREATE TABLE supla_user_login_attempt (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, email VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', ipv4 INT UNSIGNED NOT NULL, successful TINYINT(1) NOT NULL, INDEX IDX_A82AAA49A76ED395 (user_id), INDEX supla_user_login_attempt_ipv4_email_idx (email, ipv4), INDEX supla_user_login_attempt_created_at_idx (created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supla_user_login_attempt ADD CONSTRAINT FK_A82AAA49A76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id)');
        $this->addSql('INSERT INTO supla_user_login_attempt (user_id, email, ipv4, created_at, successful) SELECT id, email, last_ipv4, last_login, 1 FROM supla_user WHERE last_login IS NOT NULL');
        $this->addSql('INSERT INTO supla_user_login_attempt (user_id, email, ipv4, created_at, successful) SELECT id, email, current_ipv4, current_login, 1 FROM supla_user WHERE current_login IS NOT NULL');
        $this->addSql('ALTER TABLE supla_user DROP last_login, DROP last_ipv4, DROP current_login, DROP current_ipv4');
    }

    public function down(Schema $schema) {
        $this->abortIf(true, 'There is no way back');
    }
}
