<?php
namespace App\Entity;

use App\Enums\ActionableSubjectType;

interface HasSubject {
    public function hasSubject(): bool;

    public function getSubjectType(): ?ActionableSubjectType;

    public function getSubject(): ?ActionableSubject;
}
