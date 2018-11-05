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
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Exception\ApiException;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @method static Manufacturer UNKNOWN()
 * @method static Manufacturer ACSOFTWARE()
 * @method static Manufacturer TRANSCOM()
 * @method static Manufacturer LOGI()
 * @method static Manufacturer ZAMEL()
 * @method static Manufacturer NICE()
 * @method static Manufacturer ITEAD()
 * @method static Manufacturer VL()
 * @method static Manufacturer HPOL()
 */

final class Manufacturer extends Enum {
    const UNKNOWN = 0;
    const ACSOFTWARE = 1;
    const TRANSCOM = 2;
    const LOGI = 3;
    const ZAMEL = 4;
    const NICE = 5;
    const ITEAD = 6;
    const VL = 7;
    const HPOL = 8;

    public function __construct($value) {
        try {
            parent::__construct($value ?? 0);
        } catch (UnexpectedValueException $e) {
            $this->value = 0;
        }
    }

    /** @Groups({"basic"}) */
    public function getId(): int {
        return $this->value;
    }

    /** @Groups({"basic"}) */
    public function getName(): string {
        return $this->getKey();
    }

    /** @Groups({"basic"}) */
    public function getCaption(): string {
        return self::captions()[$this->value] ?? 'Unknown';
    }

    public static function captions(): array {
        return [
            self::UNKNOWN => 'Unknown',
            self::ACSOFTWARE => 'AC SOFTWARE SP. Z O.O.',
            self::TRANSCOM => 'TransCom International s.c.',
            self::LOGI => 'Logi',
            self::ZAMEL => 'Zamel sp. z o.o.',
            self::NICE => 'Nice Poland sp. z o.o.',
            self::ITEAD => 'ITEAD INTELLIGENT SYSTEMS CO., LTD',
            self::VL => 'VL',
            self::HPOL => 'HP',
        ];
    }

    public static function fromString(string $name): Manufacturer {
        $name = trim($name);
        try {
            if (is_numeric($name)) {
                return new self((int)$name);
            } else {
                $name = strtoupper($name);
                return self::$name();
            }
        } catch (\RuntimeException $e) {
            throw new ApiException('An incorrect manufacturer has been provided: ' . $name, 400, $e);
        }
    }

    /**
     * @param string[] $names
     * @return Manufacturer[]
     */
    public static function fromStrings(array $names): array {
        return array_map(self::class . '::fromString', $names);
    }
}
