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

use Assert\Assertion;

final class StringUtils {
    private function __construct() {
    }

    public static function snakeCaseToCamelCase(string $string): string {
        return lcfirst(str_replace('_', '', ucwords(strtolower($string), '_')));
    }

    public static function camelCaseToSnakeCase(string $string): string {
        return strtoupper(trim(preg_replace('#([A-Z])#', '_$1', $string), '_'));
    }

    public static function camelCaseToSnakeCaseLower(string $string): string {
        return strtolower(self::camelCaseToSnakeCase($string));
    }

    /** https://stackoverflow.com/a/31284266/878514 */
    public static function randomString(
        int $length,
        string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-*@!()='
    ) {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        Assertion::greaterThan($max, 1, '$keyspace must be at least two characters long');
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }

    /**
     * @see https://stackoverflow.com/a/15575293/878514
     */
    public static function joinPaths(string...$paths): string {
        $nonEmptyPaths = array_filter(
            $paths,
            function ($s) {
                return $s !== '';
            }
        );
        return self::unixSlashes(preg_replace('#([^:])/+#', '$1/', join('/', $nonEmptyPaths)));
    }

    public static function unixSlashes(?string $path): ?string {
        if (!$path) {
            return $path;
        }
        return str_replace('\\', '/', $path);
    }

    public static function isNotBlank($string): bool {
        return !is_string($string) || strlen(trim($string)) > 0;
    }
}
