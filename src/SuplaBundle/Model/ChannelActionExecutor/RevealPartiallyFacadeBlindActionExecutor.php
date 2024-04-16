<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunctionAction;

class RevealPartiallyFacadeBlindActionExecutor extends ShutPartiallyFacadeBlindActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::REVEAL_PARTIALLY();
    }

    public function execute(ActionableSubject $subject, array $params = []) {
        if (isset($params['percentage'])) {
            if ($params['percentageAsDelta']) {
                $params['percentage'] = -$params['percentage'];
            } else {
                $params['percentage'] = 100 - $params['percentage'];
            }
        }
        if (isset($params['tilt'])) {
            if ($params['tiltAsDelta']) {
                $params['tilt'] = -$params['tilt'];
            } else {
                $params['tilt'] = 100 - $params['tilt'];
            }
        }
        parent::execute($subject, $params);
    }

}
