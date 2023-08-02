<?php
namespace SuplaBundle\Serialization\RequestFiller;

use Symfony\Component\HttpFoundation\Request;

abstract class AbstractRequestFiller {
    public function fillFromRequest(Request $request, $entity = null) {
        return $this->fillFromData($request->request->all(), $entity);
    }

    abstract public function fillFromData(array $data, $entity = null);
}
