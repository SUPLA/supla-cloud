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

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class IpAddressType extends Type {
    public function getName() {
        return 'ipaddress';
    }

    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform) {
        return $platform->getIntegerTypeDeclarationSQL(array_replace($fieldDeclaration, ['unsigned' => true]));
    }

    public function canRequireSQLConversion() {
        return true;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform) {
        return true;
    }

    public function convertToPHPValueSQL($sqlExpr, $platform) {
        return sprintf('INET_NTOA(%s)', $sqlExpr);
    }

    public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform) {
        return sprintf('INET_ATON(%s)', $sqlExpr);
    }
}
