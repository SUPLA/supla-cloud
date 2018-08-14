<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Create supla_oauth_client_authorizations table. Add ON DELETE CASCADE to oauth_client's FKs.
 */
class Version20180807083217 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('CREATE TABLE supla_oauth_client_authorizations (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, client_id INT NOT NULL, scope VARCHAR(2000) NOT NULL, authorization_date DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', INDEX IDX_6B787396A76ED395 (user_id), INDEX IDX_6B78739619EB6921 (client_id), UNIQUE INDEX UNIQUE_USER_CLIENT (user_id, client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supla_oauth_client_authorizations ADD CONSTRAINT FK_6B787396A76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_oauth_client_authorizations ADD CONSTRAINT FK_6B78739619EB6921 FOREIGN KEY (client_id) REFERENCES supla_oauth_clients (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_oauth_access_tokens CHANGE scope scope VARCHAR(2000) NOT NULL');
        $this->addSql('ALTER TABLE supla_oauth_refresh_tokens CHANGE scope scope VARCHAR(2000) NOT NULL');
        $this->addSql('ALTER TABLE supla_oauth_access_tokens DROP FOREIGN KEY FK_2402564B19EB6921');
        $this->addSql('ALTER TABLE supla_oauth_access_tokens ADD CONSTRAINT FK_2402564B19EB6921 FOREIGN KEY (client_id) REFERENCES supla_oauth_clients (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_oauth_refresh_tokens DROP FOREIGN KEY FK_B809538C19EB6921');
        $this->addSql('ALTER TABLE supla_oauth_refresh_tokens ADD CONSTRAINT FK_B809538C19EB6921 FOREIGN KEY (client_id) REFERENCES supla_oauth_clients (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_oauth_auth_codes DROP FOREIGN KEY FK_48E00E5D19EB6921');
        $this->addSql('ALTER TABLE supla_oauth_auth_codes ADD CONSTRAINT FK_48E00E5D19EB6921 FOREIGN KEY (client_id) REFERENCES supla_oauth_clients (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) {
        $this->abortIf(true, 'There is no way back');
    }
}
