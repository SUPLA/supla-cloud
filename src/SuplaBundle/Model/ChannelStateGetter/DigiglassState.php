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

namespace SuplaBundle\Model\ChannelStateGetter;

use Assert\Assertion;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelType;

class DigiglassState {
    /** @var int */
    private $sectionsQuantity;
    /** @var int */
    private $mask = 0;
    /** @var int */
    private $touchedBits = 0;

    private function __construct(int $sectionsQuantity) {
        $this->sectionsQuantity = $sectionsQuantity;
    }

    public static function sections(int $sectionsQuantity): self {
        return new self($sectionsQuantity);
    }

    public static function channel(IODeviceChannel $channel): self {
        Assertion::eq(ChannelType::DIGIGLASS, $channel->getType()->getId(), 'Invalid channel type.');
        return new self($channel->getParam1() ?: 7);
    }

    /** @param int[]|int $section */
    public function setTransparent($section): self {
        if (is_array($section)) {
            array_map([$this, 'setTransparent'], $section);
        } else {
            Assertion::integerish($section);
            $this->setBit($section, true);
        }
        return $this;
    }

    /** @param int[]|int $section */
    public function setOpaque($section): self {
        if (is_array($section)) {
            array_map([$this, 'setOpaque'], $section);
        } else {
            Assertion::integerish($section);
            $this->setBit($section, false);
        }
        return $this;
    }

    private function setBit(int $section, bool $transparent) {
        Assertion::between($section, 0, $this->sectionsQuantity - 1);
        $this->touchedBits |= 1 << $section;
        if ($transparent) {
            $this->mask |= 1 << $section;
        } else {
            $this->mask &= ~(1 << $section);
        }
    }

    public function getMask(): int {
        return $this->mask;
    }

    public function setMask(int $mask): self {
        $this->mask = $mask;
        $this->touchedBits = pow(2, $this->sectionsQuantity) - 1;
        return $this;
    }

    public function getTouchedBits(): int {
        return $this->touchedBits;
    }

    public function isTouched(int $section): bool {
        return $this->touchedBits & 1 << $section;
    }

    public function isTransparent(int $section): bool {
        return $this->mask & 1 << $section;
    }

    public function isOpaque(int $section): bool {
        return !$this->isTransparent($section);
    }

    public function getTouchedSections(): array {
        return array_values(array_filter(range(0, $this->sectionsQuantity - 1), [$this, 'isTouched']));
    }

    public function getTransparentSections(): array {
        return array_values(array_filter($this->getTouchedSections(), [$this, 'isTransparent']));
    }

    public function getOpaqueSections(): array {
        return array_values(array_filter($this->getTouchedSections(), [$this, 'isOpaque']));
    }
}
