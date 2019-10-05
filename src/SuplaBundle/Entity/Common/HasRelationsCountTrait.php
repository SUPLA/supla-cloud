<?php
namespace SuplaBundle\Entity\Common;

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
}
