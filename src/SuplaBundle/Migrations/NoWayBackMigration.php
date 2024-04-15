<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace SuplaBundle\Migrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

abstract class NoWayBackMigration extends AbstractMigration implements LoggerAwareInterface {
    private LoggerInterface $migrationsLogger;

    final public function up(Schema $schema): void {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on MySQL.');
        $this->migrate();
    }

    abstract public function migrate();

    public function down(Schema $schema): void {
        $this->abortIf(true, 'There is no way back.');
    }

    protected function fetchAll(string $sqlQuery): array {
        return $this->getConnection()->fetchAllAssociative($sqlQuery);
    }

    protected function getConnection(): Connection {
        return $this->connection;
    }

    public function setLogger(LoggerInterface $logger) {
        $this->migrationsLogger = $logger;
    }

    protected function addSql(string $sql, array $params = [], array $types = []): void {
        $this->log($sql, $params);
        parent::addSql($sql, $params, $types);
    }

    protected function log(string $message, array $details = []): void {
        $this->migrationsLogger->debug($message, array_merge([
            'migration' => basename(str_replace('\\', '/', get_class($this))),
        ], $details));
    }
}
