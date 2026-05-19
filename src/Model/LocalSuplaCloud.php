<?php
namespace App\Model;

class LocalSuplaCloud extends TargetSuplaCloud {
    public function __construct(string $address) {
        parent::__construct($address, true);
    }
}
