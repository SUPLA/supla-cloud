<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\Main\IODeviceChannel;
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

    public function validateAndTransformActionParamsFromApi(ActionableSubject $subject, array $actionParams): array {
        Assertion::isInstanceOf($subject, IODeviceChannel::class, 'SET DIGIGLASS action can be executed on channels only.');
        $validParameters = array_intersect_key($actionParams, array_flip(['transparent', 'opaque', 'mask', 'activeBits']));
        Assertion::count(
            $validParameters,
            count($actionParams),
            'Invalid action parameters' // i18n
        );
        Assertion::greaterThan(count($validParameters), 0, 'You must set sections with transparent or opaque parameters or both.'); // i18n
        $validParameters = array_merge(['transparent' => [], 'opaque' => []], $validParameters);
        if (!is_array($validParameters['transparent'])) {
            $validParameters['transparent'] = array_map('trim', explode(',', $validParameters['transparent']));
        }
        if (!is_array($validParameters['opaque'])) {
            $validParameters['opaque'] = array_map('trim', explode(',', $validParameters['opaque']));
        }
        $validParameters['transparent'] = array_map('intval', array_filter($validParameters['transparent'], 'is_numeric'));
        $validParameters['opaque'] = array_map('intval', array_filter($validParameters['opaque'], 'is_numeric'));
        if (!$validParameters['transparent']) {
            unset($validParameters['transparent']);
        }
        if (!$validParameters['opaque']) {
            unset($validParameters['opaque']);
        }
        if (isset($validParameters['mask'])) {
            $validParameters['mask'] = intval($validParameters['mask']);
        }
        Assertion::greaterThan(count($validParameters), 0, 'You must set sections with transparent or opaque parameters or both.'); // i18n
        $state = $this->getDigiglassStateFromParams($subject, $validParameters);
        return $state->toArray();
    }

    public function execute(ActionableSubject $subject, array $actionParams = []) {
        $state = DigiglassState::fromArray($subject, $actionParams);
        if ($state->getActiveBits()) {
            $command = $subject->buildServerActionCommand('SET-DIGIGLASS-VALUE', [$state->getActiveBits(), $state->getMask()]);
            $this->suplaServer->executeCommand($command);
        }
    }

    private function getDigiglassStateFromParams(ActionableSubject $subject, array $actionParams): DigiglassState {
        /** @var IODeviceChannel $subject */
        $state = DigiglassState::channel($subject);
        if (isset($actionParams['mask'])) {
            $state->setMask($actionParams['mask']);
        } else {
            if (isset($actionParams['transparent'])) {
                $state->setTransparent($actionParams['transparent']);
            }
            if (isset($actionParams['opaque'])) {
                $state->setOpaque($actionParams['opaque']);
            }
        }
        return $state;
    }
}
