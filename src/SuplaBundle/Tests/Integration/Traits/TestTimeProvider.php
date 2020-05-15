<?php
namespace SuplaBundle\Tests\Integration\Traits;

use SuplaBundle\Model\TimeProvider;

class TestTimeProvider extends TimeProvider {
    private static $timestamp;

    public function getTimestamp(string $relative = null): int {
        return $relative ? strtotime($relative, self::getTimestamp()) : (self::$timestamp ?: parent::getTimestamp($relative));
    }

    public static function setTime($time) {
        if (is_string($time)) {
            self::$timestamp = strtotime($time);
        } elseif (is_int($time)) {
            self::$timestamp = $time;
        } elseif (is_null($time)) {
            self::$timestamp = null;
        } else {
            throw new \InvalidArgumentException('Unsupported time spec: ' . $time);
        }
    }

    public static function reset() {
        self::setTime(null);
    }
}
