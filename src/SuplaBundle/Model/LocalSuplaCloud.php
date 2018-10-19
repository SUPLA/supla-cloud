<?php
namespace SuplaBundle\Model;

class LocalSuplaCloud extends TargetSuplaCloud {
    public function __construct(string $address) {
        parent::__construct($address, true);
    }
}
