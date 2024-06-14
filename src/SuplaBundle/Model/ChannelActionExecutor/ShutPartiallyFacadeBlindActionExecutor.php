<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assert;
use Assert\Assertion;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Utils\StringUtils;

class ShutPartiallyFacadeBlindActionExecutor extends SingleChannelActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::SHUT_PARTIALLY();
    }

    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::CONTROLLINGTHEFACADEBLIND(),
            ChannelFunction::VERTICAL_BLIND(),
        ];
    }

    public function validateAndTransformActionParamsFromApi(ActionableSubject $subject, array $actionParams): array {
        Assertion::between(count($actionParams), 1, 2, 'Invalid number of parameters.');
        Assertion::allInArray(array_keys($actionParams), ['percentage', 'tilt']);
        $params = [];
        if (array_key_exists('percentage', $actionParams) && StringUtils::isNotBlank($actionParams['percentage'])) {
            [$percentage, $isDelta] = $this->extractDeltaParam($actionParams['percentage']);
            Assert::that($percentage)->numeric()->between(-100, 100);
            $params[$isDelta ? 'percentageDelta' : 'percentage'] = intval($percentage);
        }
        if (array_key_exists('tilt', $actionParams) && StringUtils::isNotBlank($actionParams['tilt'])) {
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
            $actionParams['tilt'] ?? $actionParams['tiltDelta'] ?? -1,
            isset($actionParams['tiltDelta']) ? 1 : 0,
        ]);
        $this->suplaServer->executeCommand($command);
    }

    public function transformActionParamsForApi(ActionableSubject $subject, array $actionParams): array {
        $params = ['percentage' => '', 'tilt' => ''];
        if (isset($actionParams['percentage'])) {
            $params['percentage'] = strval($actionParams['percentage']);
        } elseif (isset($actionParams['percentageDelta'])) {
            $params['percentage'] = $actionParams['percentageDelta'] > 0
                ? "+{$actionParams['percentageDelta']}"
                : strval($actionParams['percentageDelta']);
        }
        if (isset($actionParams['tilt'])) {
            $params['tilt'] = strval($actionParams['tilt']);
        } elseif (isset($actionParams['tiltDelta'])) {
            $params['tilt'] = $actionParams['tiltDelta'] > 0
                ? "+{$actionParams['tiltDelta']}"
                : strval($actionParams['tiltDelta']);
        }
        return $params;
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
