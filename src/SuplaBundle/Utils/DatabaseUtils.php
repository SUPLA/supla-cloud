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

namespace SuplaBundle\Utils;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\ORM\EntityManagerInterface;

final class DatabaseUtils {
    private function __construct() {
    }

    public static function turnOffQueryBuffering(EntityManagerInterface $entityManager): void {
        $entityManager->getConnection()->getNativeConnection()->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
    }

    public static function getPlatform(EntityManagerInterface $entityManager): string {
        $platform = $entityManager->getConnection()->getDatabasePlatform();
        return $platform instanceof PostgreSQLPlatform ? 'psql' : 'mysql';
    }

    public static function getTimestampFunction(EntityManagerInterface $entityManager, $field = 'date'): string {
        $platform = self::getPlatform($entityManager);
        $format = ['psql' => 'EXTRACT(EPOCH FROM %s)'][$platform] ?? 'UNIX_TIMESTAMP(%s)';
        return sprintf($format, self::quoteColumnName($entityManager, $field));
    }

    public static function quoteColumnName(EntityManagerInterface $entityManager, mixed $field): string {
        $platform = self::getPlatform($entityManager);
        $quoteChar = ['psql' => '"'][$platform] ?? '`';
        return $quoteChar . addcslashes($field, $quoteChar) . $quoteChar;
    }
}
