<?php
/*
 src/AppBundle/Doctrine/Types\Binary.php

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

namespace AppBundle\Doctrine\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * @author  Przemyslaw Zygmunt AC SOFTWARE SP. Z O.O. <p.zygmunt@acsoftware.pl>
 */
class BinaryType extends Type
{
	const BINARY = 'binary';

	public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
	{
		return sprintf('BINARY(%d)', $fieldDeclaration['length']);
	}

	public function convertToPHPValue($value, AbstractPlatform $platform)
	{
		if ($value !== null) {
			$value= unpack('H*', $value);
			return array_shift($value);
		}
	}

	public function convertToDatabaseValue($value, AbstractPlatform $platform)
	{
		if ($value !== null) {
			return pack('H*', $value);
		}
	}

	public function getName()
	{
		return self::BINARY;
	}
}

?>
