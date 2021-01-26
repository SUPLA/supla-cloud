<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Entity\HasFunction;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\ChannelStateGetter\DigiglassState;

class SetDigiglassParametersActionExecutor extends SingleChannelActionExecutor {
    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::DIGIGLASS_VERTICAL(),
            ChannelFunction::DIGIGLASS_HORIZONTAL(),
        ];
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::SET();
    }

    public function validateActionParams(HasFunction $subject, array $actionParams): array {
        Assertion::isInstanceOf($subject, IODeviceChannel::class, 'SET DIGIGLASS action can be executed on channels only.');
        $validParameters = array_intersect_key($actionParams, array_flip(['transparent', 'opaque', 'mask']));
        Assertion::count(
            $validParameters,
            count($actionParams),
            'Invalid action parameters'
        );
        Assertion::greaterThan(count($validParameters), 0, 'You must set sections with transparent or opaque parameters or both.'); // i18n
        if (isset($validParameters['mask'])) {
            return ['mask' => $validParameters['mask']];
        }
        $validParameters = array_merge(['transparent' => [], 'opaque' => []], $validParameters);
        if (!is_array($validParameters['transparent'])) {
            $validParameters['transparent'] = array_map('trim', explode(',', $validParameters['transparent']));
        }
        if (!is_array($validParameters['opaque'])) {
            $validParameters['opaque'] = array_map('trim', explode(',', $validParameters['opaque']));
        }
        $validParameters['transparent'] = array_map('intval', array_filter($validParameters['transparent'], 'is_numeric'));
        $validParameters['opaque'] = array_map('intval', array_filter($validParameters['opaque'], 'is_numeric'));
        return $validParameters;
    }

    public function execute(HasFunction $subject, array $actionParams = []) {
        /** @var IODeviceChannel $subject */
        $state = DigiglassState::channel($subject);
        if (isset($actionParams['mask'])) {
            $state->setMask($actionParams['mask']);
        }
        if (isset($actionParams['transparent'])) {
            $state->setTransparent($actionParams['transparent']);
        }
        if (isset($actionParams['opaque'])) {
            $state->setOpaque($actionParams['opaque']);
        }
        if ($state->getTouchedBits()) {
            $command = 'SET-DIGIGLASS-VALUE:' . implode(',', [
                    $subject->getUser()->getId(),
                    $subject->getIoDevice()->getId(),
                    $subject->getId(),
                    $state->getTouchedBits(),
                    $state->getMask(),
                ]);
            $this->suplaServer->executeSetCommand($command);
        }
    }
}
