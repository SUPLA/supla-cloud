<?php
namespace App\Model\ChannelStateGetter;

use App\Entity\Main\Schedule;
use OpenApi\Annotations as OA;

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
