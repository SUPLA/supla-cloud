<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Electricity Meter Log
 */
class Version20180723132652 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE supla_em_log (id INT AUTO_INCREMENT NOT NULL, channel_id INT NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', phase1_fae BIGINT NOT NULL, phase1_rae BIGINT NOT NULL, phase1_fre BIGINT NOT NULL, phase1_rre BIGINT NOT NULL, phase2_fae BIGINT NOT NULL, phase2_rae BIGINT NOT NULL, phase2_fre BIGINT NOT NULL, phase2_rre BIGINT NOT NULL, phase3_fae BIGINT NOT NULL, phase3_rae BIGINT NOT NULL, phase3_fre BIGINT NOT NULL, phase3_rre BIGINT NOT NULL, INDEX channel_id_idx (channel_id), INDEX date_idx (date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    	$this->abortIf(true, 'There is no way back');
    }
}
