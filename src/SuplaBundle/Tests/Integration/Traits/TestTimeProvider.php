<?php
namespace SuplaBundle\Tests\Integration\Traits;

use SuplaBundle\Model\TimeProvider;

class TestTimeProvider extends TimeProvider {
    private $timestamp;

    public function getTimestamp(): int {
        return $this->timestamp ? $this->timestamp : parent::getTimestamp();
    }

    public function setTimestamp($time) {
        if (is_string($time)) {
            $this->timestamp = strtotime($time);
        } else if (is_int($time)) {
            $this->timestamp = $time;
        } else {
            throw new \InvalidArgumentException('Unsupported time spec: ' . $time);
        }
    }
}
