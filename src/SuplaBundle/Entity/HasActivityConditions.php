<?php
namespace SuplaBundle\Entity;

interface HasActivityConditions {
    public function getActiveFrom(): ?\DateTime;

    public function setActiveFrom(?\DateTime $activeFrom): void;

    public function getActiveTo(): ?\DateTime;

    public function setActiveTo(?\DateTime $activeTo): void;

    public function getActiveHours(): ?array;

    public function setActiveHours(?array $activeHours): void;

    public function getActivityConditions(): array;

    public function setActivityConditions(?array $activityConditions): void;
}
