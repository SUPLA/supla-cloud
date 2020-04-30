<?php

namespace SuplaBundle\Tests\Integration\Traits;

class MysqlUtcDate {
    private $date;

    public function __construct($spec) {
        $date = $spec instanceof \DateTime ? clone $spec : new \DateTime($spec);
        $date->setTimezone(new \DateTimeZone('UTC'));
        $this->date = $date->format('Y-m-d H:i:s');
    }

    public function __toString() {
        return $this->date;
    }

    public static function toString($spec): string {
        return (string)(new self($spec));
    }
}
