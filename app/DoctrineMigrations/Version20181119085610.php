<?php

namespace Supla\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * supla_alexa_egc renamed to supla_amazon_alexa
 * endpoint_scope column size extended to 36 characters
 */
class Version20181119085610 extends NoWayBackMigration
{
    public function migrate() {
        $this->addSql('RENAME TABLE supla_alexa_egc TO supla_amazon_alexa');
        $this->addSql('ALTER TABLE supla_amazon_alexa DROP FOREIGN KEY FK_9553EE97A76ED395');
        $this->addSql('ALTER TABLE supla_amazon_alexa CHANGE endpoint_scope endpoint_scope VARCHAR(36) NOT NULL');
        $this->addSql('DROP INDEX uniq_9553ee97a76ed395 ON supla_amazon_alexa');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_290228F0A76ED395 ON supla_amazon_alexa (user_id)');
        $this->addSql('ALTER TABLE supla_amazon_alexa ADD CONSTRAINT FK_9553EE97A76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id) ON DELETE CASCADE');

    }

}

