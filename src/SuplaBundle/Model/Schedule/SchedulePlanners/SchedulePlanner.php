<?php
namespace SuplaBundle\Model\Schedule\SchedulePlanners;

use SuplaBundle\Entity\Schedule;

interface SchedulePlanner
{
    /**
     * Calculates next run date of a given schedule from the specified current date.
     *
     * @param Schedule $schedule the schedule to calculate the next run date for
     * @param \DateTime $currentDate the current date
     * @return \DateTime the next run date
     * @throws \RuntimeException if the next run date could not be calculated
     */
    public function calculateNextRunDate(Schedule $schedule, \DateTime $currentDate);


    /**
     * Checks if it can calculate the next run date for given schedule.
     *
     * @param Schedule $schedule the schedule to calculate the next run date for
     * @return boolean true if the calculation is possible, false otherwise
     */
    public function canCalculateFor(Schedule $schedule);
}
