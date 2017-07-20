<?php
namespace SuplaBundle\Tests\Model\Schedule\SchedulePlanner;

use SuplaBundle\Entity\Schedule;
use SuplaBundle\Entity\User;

class ScheduleWithTimezone extends Schedule {
    public function __construct($timeSpec = null, $timezone = 'Europe/Warsaw') {
        $user = new User();
        parent::__construct($user);
        $user->setTimezone($timezone);
        $this->setDateStart(new \DateTime());
        if ($timeSpec) {
            $this->setTimeExpression($timeSpec);
        }
    }
}
