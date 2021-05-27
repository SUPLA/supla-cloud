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

namespace SuplaBundle\Model\Doctrine;

use DateTime;
use DateTimeZone;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeType;

/**
 * Saves datetime always as UTC.
 * http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/cookbook/working-with-datetime.html
 */
class UTCDateTimeType extends DateTimeType {
    private static $utc = null;

    public function convertToDatabaseValue($value, AbstractPlatform $platform) {
        if ($value === null || is_string($value)) {
            return $value;
        }
        if (is_null(self::$utc)) {
            self::$utc = new DateTimeZone('UTC');
        }
        $value->setTimeZone(self::$utc);
        return $value->format($platform->getDateTimeFormatString());
    }

    public function convertToPHPValue($value, AbstractPlatform $platform) {
        if ($value === null) {
            return null;
        }
        if (is_null(self::$utc)) {
            self::$utc = new DateTimeZone('UTC');
        }
        $val = DateTime::createFromFormat($platform->getDateTimeFormatString(), $value, self::$utc);
        if (!$val) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }
        return $val;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform) {
        return true;
    }

    public function getName() {
        return 'utcdatetime';
    }
}
