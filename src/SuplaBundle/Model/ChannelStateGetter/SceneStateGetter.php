<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\Scene;
use SuplaBundle\Enums\SceneInitiatiorType;
use SuplaBundle\Supla\SuplaServerAware;

/**
 * @OA\Schema(schema="SceneState",
 *     description="State of scenes.",
 *     @OA\Property(property="executing", type="boolean"),
 * )
 */
class SceneStateGetter {
    use SuplaServerAware;

    public function getState(Scene $scene): array {
        [$initiatorType, $initiatorId, $initiatorNameBase64, $msFromStart, $msToEnd] = $this->suplaServer->getSceneSummary($scene);
        if ($msFromStart || $msToEnd) {
            return [
                'executing' => true,
                'initiatorTypeId' => intval($initiatorType),
                'initiatorType' => (new SceneInitiatiorType(intval($initiatorType)))->getKey(),
                'initiatorId' => intval($initiatorId),
                'initiatorName' => base64_decode($initiatorNameBase64),
                'millisecondsFromStart' => intval($msFromStart),
                'millisecondsToEnd' => intval($msToEnd),
            ];
        } else {
            return ['executing' => false];
        }
    }
}
