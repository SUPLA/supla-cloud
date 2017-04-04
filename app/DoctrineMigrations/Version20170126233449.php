<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170126233449 extends AbstractMigration {
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE supla_schedule (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, channel_id INT NOT NULL, time_expression VARCHAR(100) NOT NULL, action INT NOT NULL, action_param VARCHAR(255) DEFAULT NULL, mode VARCHAR(15) NOT NULL, date_start DATETIME NOT NULL, date_end DATETIME DEFAULT NULL, enabled TINYINT(1) NOT NULL, next_calculation_date DATETIME, INDEX IDX_323E8ABEA76ED395 (user_id), INDEX IDX_323E8ABE72F5A1AA (channel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_scheduled_executions (id INT AUTO_INCREMENT NOT NULL, schedule_id INT NOT NULL, timestamp DATETIME DEFAULT NULL, result INT DEFAULT NULL, INDEX IDX_FB21DBDCA40BC2D5 (schedule_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supla_schedule ADD CONSTRAINT FK_323E8ABEA76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id)');
        $this->addSql('ALTER TABLE supla_schedule ADD CONSTRAINT FK_323E8ABE72F5A1AA FOREIGN KEY (channel_id) REFERENCES supla_dev_channel (id)');
        $this->addSql('ALTER TABLE supla_scheduled_executions ADD CONSTRAINT FK_FB21DBDCA40BC2D5 FOREIGN KEY (schedule_id) REFERENCES supla_schedule (id)');
        $this->addSql('ALTER TABLE supla_client CHANGE guid guid VARBINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE supla_iodevice CHANGE guid guid VARBINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE supla_user ADD timezone VARCHAR(30) DEFAULT NULL, ADD limit_schedule INT DEFAULT 10 NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE supla_scheduled_executions DROP FOREIGN KEY FK_FB21DBDCA40BC2D5');
        $this->addSql('DROP TABLE supla_schedule');
        $this->addSql('DROP TABLE supla_scheduled_executions');
        $this->addSql('ALTER TABLE supla_client CHANGE guid guid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE supla_iodevice CHANGE guid guid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE supla_user DROP timezone, DROP limit_schedule');
    }
}
