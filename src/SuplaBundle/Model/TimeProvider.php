<?php
namespace SuplaBundle\Model;

class TimeProvider {
    public function getTimestamp(): int {
        return time();
    }

    public function getDateTime(\DateInterval $interval = null): \DateTime {
        $dateTime = new \DateTime('@' . $this->getTimestamp());
        if ($interval) {
            $dateTime->add($interval);
        }
        return $dateTime;
    }
}
