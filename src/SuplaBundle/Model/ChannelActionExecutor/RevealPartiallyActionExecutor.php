<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunctionAction;

class RevealPartiallyActionExecutor extends ShutPartiallyActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::REVEAL_PARTIALLY();
    }

    public function execute(ActionableSubject $subject, array $params = []) {
        if (isset($params['percentage'])) {
            $params['percentage'] = 100 - $params['percentage'];
        } elseif (isset($params['percentageDelta'])) {
            $params['percentageDelta'] = -$params['percentageDelta'];
        }
        if (isset($params['tilt'])) {
            $params['tilt'] = 100 - $params['tilt'];
        } elseif (isset($params['tiltDelta'])) {
            $params['tiltDelta'] = -$params['tiltDelta'];
        }
        parent::execute($subject, $params);
    }
}
