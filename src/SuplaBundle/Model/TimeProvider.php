<?php
namespace SuplaBundle\Model;

class TimeProvider {
    public function getTimestamp(): int {
        return time();
    }
}
