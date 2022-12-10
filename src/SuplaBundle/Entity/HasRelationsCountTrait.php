<?php
namespace SuplaBundle\Entity;

trait HasRelationsCountTrait {
    /** @var array */
    protected $relationsCount;

    public function setRelationsCount(array $relationsCount) {
        $this->relationsCount = $relationsCount;
    }

    /** @return array|null */
    public function getRelationsCount() {
        return $this->relationsCount;
    }

    public function clearRelationsCount(): self {
        $this->relationsCount = null;
        return $this;
    }
}
