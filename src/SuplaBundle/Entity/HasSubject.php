<?php
namespace SuplaBundle\Entity;

use SuplaBundle\Enums\ActionableSubjectType;

interface HasSubject {
    public function hasSubject(): bool;

    public function getSubjectType(): ?ActionableSubjectType;

    public function getSubject(): ?ActionableSubject;
}
