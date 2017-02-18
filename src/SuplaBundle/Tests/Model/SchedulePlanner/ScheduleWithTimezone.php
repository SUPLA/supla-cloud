<?php
namespace SuplaBundle\Tests\Model\SchedulePlanner;


use SuplaBundle\Entity\Schedule;
use SuplaBundle\Entity\User;

class ScheduleWithTimezone extends Schedule
{
    public function __construct($timeSpec = null, $timezone = 'Europe/Warsaw')
    {
        $user = new User();
        $user->setTimezone($timezone);
        $this->setUser($user);
        if ($timeSpec) {
            $this->setCronExpression($timeSpec);
        }
    }
}
