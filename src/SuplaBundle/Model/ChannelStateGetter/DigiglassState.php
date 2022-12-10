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
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelType;

class DigiglassState {
    /** @var int */
    private $sectionsQuantity;
    /** @var int */
    private $mask = 0;
    /** @var int */
    private $activeBits = 0;

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

    public static function fromArray(IODeviceChannel $channel, array $array): self {
        $state = self::channel($channel);
        $state->mask = $array['mask'];
        $state->activeBits = $array['activeBits'];
        return $state;
    }

    public function toArray(): array {
        return [
            'mask' => $this->getMask(),
            'activeBits' => $this->getActiveBits(),
            'transparent' => $this->getTransparentSections(),
            'opaque' => $this->getOpaqueSections(),
        ];
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
        $this->activeBits |= 1 << $section;
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
        $this->activeBits = pow(2, $this->sectionsQuantity) - 1;
        return $this;
    }

    public function getActiveBits(): int {
        return $this->activeBits;
    }

    public function isActive(int $section): bool {
        return $this->activeBits & 1 << $section;
    }

    public function isTransparent(int $section): bool {
        return $this->mask & 1 << $section;
    }

    public function isOpaque(int $section): bool {
        return !$this->isTransparent($section);
    }

    public function getActiveSections(): array {
        return array_values(array_filter(range(0, $this->sectionsQuantity - 1), [$this, 'isActive']));
    }

    public function getTransparentSections(): array {
        return array_values(array_filter($this->getActiveSections(), [$this, 'isTransparent']));
    }

    public function getOpaqueSections(): array {
        return array_values(array_filter($this->getActiveSections(), [$this, 'isOpaque']));
    }
}
