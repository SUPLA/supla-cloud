<?php
namespace SuplaBundle\Entity;

use SuplaBundle\Enums\ActionableSubjectType;

interface HasSubject {
    public function getSubjectType(): ActionableSubjectType;

    public function getSubject(): ?HasFunction;
}
