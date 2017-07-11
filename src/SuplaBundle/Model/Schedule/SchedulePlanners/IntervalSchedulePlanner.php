<?php
namespace SuplaBundle\Model\Schedule\SchedulePlanners;

use SuplaBundle\Entity\Schedule;

class IntervalSchedulePlanner implements SchedulePlanner {

    const CRON_EXPRESSION_INTERVAL_REGEX = '#^\*/(\d{1,3})( \*)*$#';

    public function calculateNextRunDate(Schedule $schedule, \DateTime $currentDate) {
        preg_match(self::CRON_EXPRESSION_INTERVAL_REGEX, $schedule->getTimeExpression(), $matches);
        $intervalInMinutes = intval($matches[1]);
        $period = "PT{$intervalInMinutes}M";
        $nextRunDate = clone $currentDate;
        $nextRunDate->add(new \DateInterval($period));
        return CompositeSchedulePlanner::roundToClosest5Minutes($nextRunDate, $schedule->getUserTimezone());
    }

    public function canCalculateFor(Schedule $schedule) {
        return !!preg_match(self::CRON_EXPRESSION_INTERVAL_REGEX, $schedule->getTimeExpression());
    }
}
