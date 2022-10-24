<?php

namespace SuplaBundle\Entity;

use Assert\Assertion;

final class ActiveHours {
    private $activeHours;

    private function __construct($activeHours) {
        if (is_string($activeHours)) {
            $this->activeHours = $activeHours;
        } elseif (is_array($activeHours)) {
            $databaseRepresentation = [];
            foreach ($activeHours as $weekday => $hours) {
                Assertion::between($weekday, 1, 7, 'Weekday must be between 1 and 7.');
                Assertion::isArray($hours);
                Assertion::allBetween($hours, 0, 23, 'All hours should be between 0 and 23.');
                $hours = array_map(function ($hour) use ($weekday) {
                    return $weekday . $hour;
                }, array_unique($hours));
                $databaseRepresentation = array_merge($databaseRepresentation, $hours);
            }
            if ($databaseRepresentation) {
                $this->activeHours = ',' . implode(',', $databaseRepresentation) . ',';
            }
        } else {
            $this->activeHours = null;
        }
    }

    public static function fromString(?string $activeHours): self {
        return new self($activeHours);
    }

    public static function fromArray(?array $activeHours): self {
        return new self($activeHours);
    }

    public function toString(): ?string {
        return $this->activeHours;
    }

    public function toArray(): ?array {
        if ($this->activeHours) {
            $defs = explode(',', $this->activeHours);
            $activeHours = [];
            foreach ($defs as $def) {
                if ($def) {
                    $weekday = intval(((string)$def)[0]);
                    $hour = intval(substr((string)$def, 1));
                    $activeHours[$weekday][] = $hour;
                }
            }
            return $activeHours;
        } else {
            return null;
        }
    }
}
