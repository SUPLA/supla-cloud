<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\Main\Scene;
use SuplaBundle\Enums\SceneInitiatiorType;
use SuplaBundle\Supla\SuplaServerAware;

/**
 * @OA\Schema(schema="SceneState",
 *     description="State of a scene.",
 *     required={"executing"},
 *     @OA\Property(property="executing", type="boolean"),
 *     @OA\Property(property="initiatorTypeId", type="integer", enum={0,1,2,3,4,5,6,7,8}),
 *     @OA\Property(property="initiatorType", type="string", enum={"UNKNOWN", "DEVICE", "CLIENT", "IPC", "MQTT", "AMAZON_ALEXA", "GOOGLE_HOME", "ACTION_TRIGGER", "SCENE"}),
 *     @OA\Property(property="initiatorId", type="integer", nullable=true),
 *     @OA\Property(property="initiatorName", type="string", nullable=true),
 *     @OA\Property(property="millisecondsFromStart", type="integer"),
 *     @OA\Property(property="millisecondsToEnd", type="integer"),
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
