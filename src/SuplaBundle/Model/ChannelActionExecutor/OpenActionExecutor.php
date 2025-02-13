<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace SuplaBundle\Model\ChannelActionExecutor;

use SuplaBundle\Auth\Token\WebappToken;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\ChannelStateGetter\ValveChannelStateGetter;
use SuplaBundle\Model\CurrentUserAware;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class OpenActionExecutor extends SetCharValueActionExecutor {
    use CurrentUserAware;

    /** @var ValveChannelStateGetter */
    private $valveManuallyShutChannelStateGetter;

    public function __construct(ValveChannelStateGetter $valveManuallyShutChannelStateGetter) {
        $this->valveManuallyShutChannelStateGetter = $valveManuallyShutChannelStateGetter;
    }

    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::CONTROLLINGTHEGATEWAYLOCK(),
            ChannelFunction::CONTROLLINGTHEDOORLOCK(),
            ChannelFunction::VALVEOPENCLOSE(),
        ];
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::OPEN();
    }

    public function validateAndTransformActionParamsFromApi(ActionableSubject $subject, array $actionParams): array {
        if ($subject->getFunction()->getId() == ChannelFunction::VALVEOPENCLOSE && $subject instanceof IODeviceChannel) {
            $state = $this->valveManuallyShutChannelStateGetter->getState($subject);
            $manuallyClosed = $state['manuallyClosed'] ?? true;
            $flooding = $state['flooding'] ?? true;
            if ($manuallyClosed || $flooding) {
                $userToken = $this->getCurrentUserToken();
                if (!$userToken instanceof WebappToken) {
                    throw new ConflictHttpException(
                        'The valve cannot be opened via a direct link or via API once it has been closed manually. ' .
                        'To resume control, open the valve manually.'
                    );
                }
            }
        }
        return parent::validateAndTransformActionParamsFromApi($subject, $actionParams);
    }
}
