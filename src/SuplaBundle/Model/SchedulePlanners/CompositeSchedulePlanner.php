<?php
namespace SuplaBundle\Model\SchedulePlanners;

use Cron\CronExpression;
use SensioLabs\Security\Exception\RuntimeException;
use SuplaBundle\Entity\Schedule;

class CompositeSchedulePlanner
{
    /** @var SchedulePlanner[] */
    private $planners;

    public function __construct(array $planners)
    {
        $this->planners = $planners;
    }

    public function calculateNextRunDate(Schedule $schedule, $currentDate = 'now')
    {
        if (!($currentDate instanceof \DateTime)) {
            $currentDate = new \DateTime($currentDate, $schedule->getUserTimezone());
        }
        foreach ($this->planners as $planner) {
            if ($planner->canCalculateFor($schedule)) {
                return $planner->calculateNextRunDate($schedule, $currentDate);
            }
        }
        throw new RuntimeException("Could not calculate the next run date for the Schedule#{$schedule->getId()}. Expression: {$schedule->getTimeExpression()}");
    }

    /**
     * @param Schedule $schedule
     * @param string $currentDate
     * @param string $until
     * @param int $maxCount
     * @return \DateTime[]
     */
    public function calculateNextRunDatesUntil(Schedule $schedule, $until = '+5days', $currentDate = 'now', $maxCount = PHP_INT_MAX)
    {
        $until = is_int($until) ? $until : strtotime($until) + 1; // +1 to make it inclusive
        $runDates = [];
        $nextRunDate = $currentDate;
        try {
            do {
                $nextRunDate = $this->calculateNextRunDate($schedule, $nextRunDate);
                $runDates[] = $nextRunDate;
            } while ($nextRunDate->getTimestamp() < $until && count($runDates) < $maxCount);
        } catch (\RuntimeException $e) {
            // impossible cron expression
        }
        return $runDates;
    }
}
