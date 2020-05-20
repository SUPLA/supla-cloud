<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Auth\Token\WebappToken;
use SuplaBundle\Entity\HasFunction;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\ChannelStateGetter\ValveManuallyClosedChannelStateGetter;
use SuplaBundle\Model\CurrentUserAware;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class OpenActionExecutor extends SetCharValueActionExecutor {
    use CurrentUserAware;

    /** @var ValveManuallyClosedChannelStateGetter */
    private $valveManuallyShutChannelStateGetter;

    public function __construct(ValveManuallyClosedChannelStateGetter $valveManuallyShutChannelStateGetter) {
        $this->valveManuallyShutChannelStateGetter = $valveManuallyShutChannelStateGetter;
    }

    protected function getCharValue(HasFunction $subject, array $actionParams = []): int {
        if ($this->isOpenCloseSubject($subject)) {
            return 2;
        } else {
            return 1;
        }
    }

    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::CONTROLLINGTHEGATEWAYLOCK(),
            ChannelFunction::CONTROLLINGTHEDOORLOCK(),
            ChannelFunction::CONTROLLINGTHEGARAGEDOOR(),
            ChannelFunction::CONTROLLINGTHEGATE(),
            ChannelFunction::VALVEOPENCLOSE(),
        ];
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::OPEN();
    }

    private function isOpenCloseSubject(HasFunction $subject): bool {
        return $this->isGateSubject($subject) || in_array($subject->getFunction()->getId(), [ChannelFunction::VALVEOPENCLOSE]);
    }

    private function isGateSubject(HasFunction $subject): bool {
        return in_array(
            $subject->getFunction()->getId(),
            [ChannelFunction::CONTROLLINGTHEGATE, ChannelFunction::CONTROLLINGTHEGARAGEDOOR]
        );
    }

    public function validateActionParams(HasFunction $subject, array $actionParams): array {
        Assertion::true(
            !$this->isGateSubject($subject) || $subject instanceof IODeviceChannel,
            "Cannot execute the requested action OPEN on channel group."
        );
        if ($subject->getFunction()->getId() == ChannelFunction::VALVEOPENCLOSE) {
            $manuallyClosed = $this->valveManuallyShutChannelStateGetter->getState($subject)['manuallyClosed'];
            if ($manuallyClosed) {
                $userToken = $this->getCurrentUserToken();
                if (!$userToken instanceof WebappToken) {
                    throw new ConflictHttpException('Prevented to open manually shut valve. Open it manually or through the app.');
                }
            }
        }
        return parent::validateActionParams($subject, $actionParams);
    }
}
