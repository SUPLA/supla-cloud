<?php
namespace SuplaBundle\Entity;

use SuplaBundle\Enums\ActionableSubjectType;

interface HasSubject {
    public function getSubjectType(): ActionableSubjectType;

    /** @return HasFunction|null */
    public function getSubject();
}
