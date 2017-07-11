<?php
namespace SuplaBundle\Model\Schedule\SchedulePlanners;

use Cron\CronExpression;
use SuplaBundle\Entity\Schedule;

class SunriseSunsetSchedulePlanner implements SchedulePlanner {

    // SR -> SunRise, SS -> SunSet
    const SPECIFICATION_REGEX = '#^S([SR])(-?\d+)#';

    /** @inheritdoc */
    public function calculateNextRunDate(Schedule $schedule, \DateTime $currentDate) {
        return CompositeSchedulePlanner::wrapInScheduleTimezone($schedule, function () use ($schedule, $currentDate) {
            $currentDate = $this->getNextDueDate($schedule, $currentDate);
            $nextRunDate = $this->calculateNextRunDateBasedOnSun($schedule, $currentDate);
            $retries = 5; // PHP sometimes returns past sunset even if we query for midnight of the next day...
            $calculatingFromDate = clone max($nextRunDate, $currentDate);
            while ((($nextRunDate <= $currentDate) || (!$this->isDue($schedule, $nextRunDate))) && --$retries) {
                $nextRunDate->setTime(0, 0);
                while ($nextRunDate <= $calculatingFromDate) {
                    $nextRunDate->add(new \DateInterval('P1D'));
                }
                $calculatingFromDate = $this->getNextDueDate($schedule, $nextRunDate);
                $nextRunDate = $this->calculateNextRunDateBasedOnSun($schedule, $calculatingFromDate);
                while ($nextRunDate <= $calculatingFromDate) {
                    $nextRunDate->add(new \DateInterval('P1D'));
                }
            }
            return $nextRunDate;
        });
    }

    private function calculateNextRunDateBasedOnSun(Schedule $schedule, \DateTime $currentDate) {
        preg_match(self::SPECIFICATION_REGEX, $schedule->getTimeExpression(), $matches);
        $location = $schedule->getUserTimezone()->getLocation();
        $function = 'date_' . ($matches[1] == 'S' ? 'sunset' : 'sunrise');
        $nextSun = $function($currentDate->getTimestamp(), SUNFUNCS_RET_TIMESTAMP, $location['latitude'], $location['longitude']);
        $nextSun += intval($matches[2]) * 60;
        $nextSunRoundTo5Minutes = round($nextSun / 300) * 300;
        $nextRunDate = (new \DateTime('now', $schedule->getUserTimezone()))->setTimestamp($nextSunRoundTo5Minutes);
        if ($nextRunDate < $currentDate) {
            // PHP sometimes returns past sunset even if we query for midnight of the next day...
            $nextRunDate->add(new \DateInterval('P1D'));
        } elseif ($nextRunDate == $currentDate) {
            // if it is sunset now, let's calculate for the next day
            return $this->calculateNextRunDateBasedOnSun($schedule, $nextRunDate->add(new \DateInterval('PT12H')));
        }
        return $nextRunDate;
    }

    /** @return \DateTime */
    private function getNextDueDate(Schedule $schedule, \DateTime $currentDate) {
        if (!$this->isDue($schedule, $currentDate)) {
            return $this->getEveryMinuteCronExpression($schedule)->getNextRunDate($currentDate);
        }
        return $currentDate;
    }

    private function isDue(Schedule $schedule, \DateTime $currentDate) {
        return $this->getEveryMinuteCronExpression($schedule)->isDue($currentDate);
    }

    /** @return CronExpression */
    private function getEveryMinuteCronExpression(Schedule $schedule) {
        $parts = explode(' ', $schedule->getTimeExpression());
        $parts[0] = '*';
        $parts[1] = '*';
        return CronExpression::factory(implode(' ', $parts));
    }

    public function canCalculateFor(Schedule $schedule) {
        return !!preg_match(self::SPECIFICATION_REGEX, $schedule->getTimeExpression());
    }
}
