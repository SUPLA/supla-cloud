<?php
namespace SuplaBundle\Model;

class TimeProvider {
    public function getTimestamp(string $relative = null): int {
        return $relative ? strtotime($relative) : time();
    }

    public function getDateTime(\DateInterval $interval = null): \DateTime {
        $dateTime = new \DateTime('@' . $this->getTimestamp());
        if ($interval) {
            $dateTime->add($interval);
        }
        return $dateTime;
    }
}
