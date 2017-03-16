<?php

namespace SuplaBundle\Enums;

use MyCLabs\Enum\Enum;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @method static ScheduleMode ONCE()
 * @method static ScheduleMode MINUTELY()
 * @method static ScheduleMode HOURLY()
 * @method static ScheduleMode DAILY()
 */
final class ScheduleMode extends Enum {
    const ONCE = 'once';
    const MINUTELY = 'minutely';
    const HOURLY = 'hourly';
    const DAILY = 'daily';

    /**
     * @Groups({"basic", "flat"})
     */
    public function getValue() {
        return parent::getValue();
    }
}
