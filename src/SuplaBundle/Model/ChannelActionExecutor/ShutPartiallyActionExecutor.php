<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assert;
use Assert\Assertion;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Utils\StringUtils;

/**
 * @OA\Schema(schema="ChannelActionParamsPercentage",
 *     description="Action params for `REVEAL`, `REVEAL_PARTIALLY`, `SHUT` or `SHUT_PARTIALLY` actions.",
 *     @OA\Property(property="percentage", type="integer", minimum=0, maximum=100),
 * )
 * @OA\Schema(schema="ChannelActionParamsPercentageAndTilt",
 *     description="Action params for `REVEAL`, `REVEAL_PARTIALLY`, `SHUT` or `SHUT_PARTIALLY` actions.",
 *     @OA\Property(property="percentage", type="integer", minimum=0, maximum=100),
 *     @OA\Property(property="tilt", type="integer", minimum=0, maximum=100),
 * )
 */
class ShutPartiallyActionExecutor extends SingleChannelActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::SHUT_PARTIALLY();
    }

    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::CONTROLLINGTHEFACADEBLIND(),
            ChannelFunction::VERTICAL_BLIND(),
            ChannelFunction::CONTROLLINGTHEROLLERSHUTTER(),
            ChannelFunction::CONTROLLINGTHEROOFWINDOW(),
            ChannelFunction::TERRACE_AWNING(),
            ChannelFunction::PROJECTOR_SCREEN(),
            ChannelFunction::CURTAIN(),
            ChannelFunction::ROLLER_GARAGE_DOOR(),
        ];
    }

    public function validateAndTransformActionParamsFromApi(ActionableSubject $subject, array $actionParams): array {
        Assertion::between(count($actionParams), 1, 2, 'Invalid number of parameters.');
        Assertion::allInArray(array_keys($actionParams), ['percentage', 'percent', 'tilt']);
        $params = [];
        if (array_key_exists('percent', $actionParams)) {
            $actionParams['percentage'] = $actionParams['percent'];
            unset($actionParams['percent']);
        }
        if (array_key_exists('percentage', $actionParams) && StringUtils::isNotBlank($actionParams['percentage'])) {
            [$percentage, $isDelta] = $this->extractDeltaParam($actionParams['percentage']);
            Assert::that($percentage)->numeric()->between(-100, 100);
            $params[$isDelta ? 'percentageDelta' : 'percentage'] = intval($percentage);
        }
        if ($this->hasTilt($subject) && array_key_exists('tilt', $actionParams) && StringUtils::isNotBlank($actionParams['tilt'])) {
            [$tilt, $isDelta] = $this->extractDeltaParam($actionParams['tilt']);
            Assert::that($tilt)->numeric()->between(-100, 100);
            $params[$isDelta ? 'tiltDelta' : 'tilt'] = intval($tilt);
        }
        Assertion::true(count($params) > 0, 'You have to set either percentage and/or tilt.');
        return $params;
    }

    public function execute(ActionableSubject $subject, array $actionParams = []) {
        // ACTION-SHUT-PARTIALLY:userId,deviceId,channelId,percentage,percentageAsDelta,tilt,tiltAsDelta
        $command = $subject->buildServerActionCommand('ACTION-SHUT-PARTIALLY', [
            $actionParams['percentage'] ?? $actionParams['percentageDelta'] ?? -1,
            isset($actionParams['percentageDelta']) ? 1 : 0,
            $this->hasTilt($subject) ? $actionParams['tilt'] ?? $actionParams['tiltDelta'] ?? -1 : -1,
            isset($actionParams['tiltDelta']) ? 1 : 0,
        ]);
        $this->suplaServer->executeCommand($command);
    }

    public function transformActionParamsForApi(ActionableSubject $subject, array $actionParams): array {
        $params = [];
        if (isset($actionParams['percentage'])) {
            $params['percentage'] = strval($actionParams['percentage']);
        } elseif (isset($actionParams['percentageDelta'])) {
            $params['percentage'] = $actionParams['percentageDelta'] >= 0
                ? "+{$actionParams['percentageDelta']}"
                : strval($actionParams['percentageDelta']);
        }
        if ($this->hasTilt($subject)) {
            if (isset($actionParams['tilt'])) {
                $params['tilt'] = strval($actionParams['tilt']);
            } elseif (isset($actionParams['tiltDelta'])) {
                $params['tilt'] = $actionParams['tiltDelta'] >= 0
                    ? "+{$actionParams['tiltDelta']}"
                    : strval($actionParams['tiltDelta']);
            }
        }
        return $params;
    }

    private function hasTilt(ActionableSubject $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::CONTROLLINGTHEFACADEBLIND,
            ChannelFunction::VERTICAL_BLIND,
        ]);
    }

    private function extractDeltaParam($param): array {
        $isDelta = false;
        if (is_string($param) && in_array($param[0], ['+', ' ', '-'])) {
            $isDelta = true;
            $param = ltrim($param, '+ ');
        }
        Assertion::numeric($param);
        $param = intval($param);
        return [$param, $isDelta || $param < 0];
    }
}
