<?php
namespace SuplaBundle\Entity\Main;

use SuplaBundle\Enums\ActionableSubjectType;

interface HasSubject {
    public function getSubjectType(): ActionableSubjectType;

    /** @return HasFunction|null */
    public function getSubject();
}
