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

namespace Supla\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

abstract class NoWayBackMigration extends AbstractMigration implements ContainerAwareInterface {
    use ContainerAwareTrait;

    public function up(Schema $schema) {
        $this->skipIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on MySQL.');
        $this->migrate();
    }

    abstract public function migrate();

    public function down(Schema $schema) {
        $this->abortIf(true, 'There is no way back.');
    }

    protected function fetchAll(string $sqlQuery): array {
        return $this->getConnection()->fetchAll($sqlQuery);
    }

    protected function getConnection(): \Doctrine\DBAL\Connection {
        return $this->container->get('doctrine.orm.entity_manager')->getConnection();
    }
}
