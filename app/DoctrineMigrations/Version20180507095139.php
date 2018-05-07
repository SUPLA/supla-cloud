<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * DELETE CASCADE for FK_EFE348F4A76ED395
 */
class Version20180507095139 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `supla_audit` DROP FOREIGN KEY `FK_EFE348F4A76ED395`');
        $this->addSql('ALTER TABLE `supla_audit` ADD CONSTRAINT `FK_EFE348F4A76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user`(`id`) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    	$this->abortIf(true, 'There is no way back');
    }
}
