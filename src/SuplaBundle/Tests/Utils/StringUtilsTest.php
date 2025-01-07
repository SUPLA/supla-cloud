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

namespace SuplaBundle\Tests\Utils;

use PHPUnit\Framework\TestCase;
use SuplaBundle\Utils\StringUtils;

class StringUtilsTest extends TestCase {
    /** @dataProvider snakeCaseToCamelCaseExamples */
    public function testSnakeCaseToCamelCase(string $snakeCase, string $camelCase) {
        $this->assertEquals($camelCase, StringUtils::snakeCaseToCamelCase($snakeCase));
    }

    /** @dataProvider snakeCaseToCamelCaseExamples */
    public function testCamelCaseToSnakeCase(string $snakeCase, string $camelCase) {
        $this->assertEquals($snakeCase, StringUtils::camelCaseToSnakeCase($camelCase));
    }

    public static function snakeCaseToCamelCaseExamples(): array {
        return [
            ['', ''],
            ['FREQUENCY', 'frequency'],
            ['POWER_ACTIVE', 'powerActive'],
            ['FORWARD_REACTIVE_ENERGY', 'forwardReactiveEnergy'],
        ];
    }

    public function testJoinPaths() {
        $this->assertEquals('a/b/c', StringUtils::joinPaths('a', 'b', 'c'));
        $this->assertEquals('a/b/c', StringUtils::joinPaths('a', '/b', 'c'));
        $this->assertEquals('a/b/c', StringUtils::joinPaths('a', '/b/', '/c'));
        $this->assertEquals('a/b/c', StringUtils::joinPaths('a', '/b//', '/c'));
        $this->assertEquals('https://supla.local/a/b', StringUtils::joinPaths('https://supla.local', 'a', 'b'));
        $this->assertEquals('http://supla.local/a/b', StringUtils::joinPaths('http://supla.local', 'a', 'b'));
        $this->assertEquals('/a/b/c', StringUtils::joinPaths('/a', 'b', 'c'));
    }
}
