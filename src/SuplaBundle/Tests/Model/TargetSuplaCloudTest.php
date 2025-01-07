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

namespace SuplaBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use SuplaBundle\Model\TargetSuplaCloud;

class TargetSuplaCloudTest extends TestCase {

    /** @dataProvider creatingFromDomainNameData */
    public function testCreatingFromDomainName(string $scheme, $domainName, $expectedAddress) {
        if (!$expectedAddress) {
            $this->expectException(\InvalidArgumentException::class);
        }
        $tc = TargetSuplaCloud::forHost($scheme, $domainName);
        $this->assertEquals($expectedAddress, $tc->getAddress());
    }

    public static function creatingFromDomainNameData() {
        return [
            ['https', 'supla.org', 'https://supla.org'],
            ['http', 'supla.org', 'http://supla.org'],
            ['https', 'https://supla.org', 'https://supla.org'],
            ['https', 'https.supla.org', 'https://https.supla.org'],
            ['https', 'supla.org:123', 'https://supla.org:123'],
            ['https', 'supla.org/abc', 'https://supla.org'],
            ['https', 'supla.org:123/abc', 'https://supla.org:123'],
            ['https', 'http://supla.org', null],
            ['https', null, null],
            ['https', '', null],
            ['https', '  ', null],
            ['https', '?', null],
            ['https', 'abc', null],
        ];
    }
}
