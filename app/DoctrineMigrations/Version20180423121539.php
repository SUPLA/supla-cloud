<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * New field and procedure, for demo server purposes
 */
class Version20180423121539 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE supla_client ADD disable_after_date DATETIME COMMENT \'(DC2Type:utcdatetime)\'');
        
        $this->addSql(<<<CREATE_PROCEDURE
			CREATE PROCEDURE `supla_on_newclient`(IN `_client_id` INT)
			    NO SQL
			BEGIN	
			END;
CREATE_PROCEDURE
        		);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    	$this->abortIf(true, 'There is no way back');
    }
}
