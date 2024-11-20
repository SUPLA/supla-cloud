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

use SuplaBundle\Exception\ApiExceptionWithDetails;
use Symfony\Component\HttpFoundation\Response;

final class ArrayUtils {
    private function __construct() {
    }

    /** @see https://stackoverflow.com/a/14972389/878514 */
    public static function flattenOnce(array $array): array {
        return call_user_func_array('array_merge', $array);
    }

    public static function leaveKeys(array $array, array $keys): array {
        return array_intersect_key($array, array_flip($keys));
    }

    public static function mergeConfigs(array $old, array $new, array $current): array {
        foreach ($new as $settingName => $newValue) {
            $beforeValue = $old[$settingName] ?? null;
            $currentValue = $current[$settingName] ?? null;
            if ($beforeValue !== $newValue && $currentValue !== $newValue) {
                if ($currentValue !== $beforeValue) {
                    throw new ApiExceptionWithDetails(
                        'Config has been changed externally.',
                        ['config' => $current, 'conflictingField' => $settingName],
                        Response::HTTP_CONFLICT
                    );
                }
            } else {
                unset($new[$settingName]);
            }
        }
        return $new;
    }

    public static function filter(array $array, callable $filter): array {
        return array_values(array_filter($array, $filter));
    }
}
