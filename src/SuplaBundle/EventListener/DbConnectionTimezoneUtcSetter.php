<?php

namespace SuplaBundle\EventListener;

use Doctrine\DBAL\Event\ConnectionEventArgs;

class DbConnectionTimezoneUtcSetter {
    public function postConnect(ConnectionEventArgs $args) {
        $args->getConnection()->executeStatement("SET @@time_zone = '+00:00';");
    }
}
