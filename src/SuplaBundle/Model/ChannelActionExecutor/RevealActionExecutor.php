<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunctionAction;

/**
 * @OA\Schema(schema="ChannelActionParamsPercentage",
 *     description="Action params for `REVEAL`, `REVEAL_PARTIALLY`, `SHUT` or `SHUT_PARTIALLY` actions.",
 *     @OA\Property(property="percentage", type="integer", minimum=0, maximum=100),
 * )
 */
class RevealActionExecutor extends ShutActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::REVEAL();
    }

    protected function getCharValue(ActionableSubject $subject, array $actionParams = []): int {
        $percent = $actionParams['percentage'] ?? 100;
        return 110 - $percent;
    }
}
