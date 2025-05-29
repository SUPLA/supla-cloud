<?php

namespace SuplaBundle\EventListener;

use Doctrine\DBAL\Event\ConnectionEventArgs;
use Doctrine\DBAL\Platforms\MySQLPlatform;

class DbConnectionTimezoneUtcSetter {
    public function postConnect(ConnectionEventArgs $args) {
        $connection = $args->getConnection();
        if ($connection->getDatabasePlatform() instanceof MySQLPlatform) {
            $connection->executeStatement("SET @@time_zone = '+00:00';");
        } else {
            $connection->executeStatement("SET TIME ZONE 'UTC';");
        }
    }
}
