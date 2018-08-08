<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use SuplaBundle\Enums\ApiClientType;

/**
 * Set all existing API clients to USER type.
 */
class Version20180707221458 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('UPDATE supla_oauth_clients SET type = ' . ApiClientType::USER);
    }

    public function down(Schema $schema) {
        $this->abortIf(true, 'There is no way back');
    }
}
