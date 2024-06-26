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

namespace SuplaBundle\Enums;

use MyCLabs\Enum\Enum;

abstract class ChannelFunctionBits extends Enum {
    public function isOn(?int $flags): bool {
        return $this->isSupported($flags);
    }

    public function isSupported(?int $flags): bool {
        return ($flags ?: 0) & $this->getValue();
    }

    public static function getSupportedFeatures(int $flags): array {
        $supportedFeatures = [];
        foreach (self::values() as $bit) {
            if ($bit->isSupported($flags)) {
                $supportedFeatures[] = $bit;
            }
        }
        return $supportedFeatures;
    }

    public static function getSupportedFeaturesNames(int $flags): array {
        return array_map(function ($bit) {
            return $bit->getKey();
        }, self::getSupportedFeatures($flags));
    }

    public static function getAllFeaturesFlag(): int {
        $flag = 0;
        foreach (self::values() as $value) {
            $flag |= $value->getValue();
        }
        return $flag;
    }
}
