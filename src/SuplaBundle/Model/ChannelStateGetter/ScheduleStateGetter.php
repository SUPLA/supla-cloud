<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\Main\Schedule;

/**
 * @OA\Schema(schema="ScheduleState",
 *     description="State of a schedule.",
 *     @OA\Property(property="enabled", type="boolean"),
 * )
 */
class ScheduleStateGetter {
    public function getState(Schedule $schedule): array {
        return [
            'enabled' => $schedule->getEnabled(),
        ];
    }
}
