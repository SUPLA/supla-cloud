<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunctionAction;

class RevealPartiallyFacadeBlindActionExecutor extends ShutPartiallyFacadeBlindActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::REVEAL_PARTIALLY();
    }

    public function validateAndTransformActionParamsFromApi(ActionableSubject $subject, array $actionParams): array {
        $params = parent::validateAndTransformActionParamsFromApi($subject, $actionParams);
        if (isset($params['percentage'])) {
            if ($params['percentageDelta']) {
                $params['percentage'] = -$params['percentage'];
            } else {
                $params['percentage'] = 100 - $params['percentage'];
            }
        }
        if (isset($params['tilt'])) {
            if ($params['tiltDelta']) {
                $params['tilt'] = -$params['tilt'];
            } else {
                $params['tilt'] = 100 - $params['tilt'];
            }
        }
        return $params;
    }
}
