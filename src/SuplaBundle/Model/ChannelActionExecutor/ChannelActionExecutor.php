<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Exception\ApiException;
use SuplaBundle\Exception\ApiExceptionWithDetails;
use SuplaBundle\Supla\SuplaServerAware;

/**
 * @OA\Schema(schema="ChannelActionParams", description="Parameters required to execute an action.",
 *   oneOf={
 *     @OA\Schema(type="object"),
 *     @OA\Schema(ref="#/components/schemas/ChannelActionParamsPercentage"),
 *     @OA\Schema(ref="#/components/schemas/ChannelActionParamsDimmer"),
 *     @OA\Schema(ref="#/components/schemas/ChannelActionParamsRgbw"),
 *     @OA\Schema(ref="#/components/schemas/ChannelActionParamsCopy"),
 *     @OA\Schema(ref="#/components/schemas/ChannelActionParamsSend"),
 *     @OA\Schema(ref="#/components/schemas/ChannelActionParamsDuration"),
 *   }
 * )
 */
class ChannelActionExecutor {
    use SuplaServerAware;

    private const INTEGRATIONS_ACTION_PARAMS = ['alexaCorrelationToken', 'googleRequestId', 'googleUserConfirmation', 'googlePin'];

    /** @var SingleChannelActionExecutor[][] */
    private $actionExecutors = [];

    /** @param SingleChannelActionExecutor[] $actionExecutors */
    public function __construct($actionExecutors) {
        foreach ($actionExecutors as $actionExecutor) {
            $this->actionExecutors[$actionExecutor->getSupportedAction()->getName()][] = $actionExecutor;
        }
    }

    public function executeAction(ActionableSubject $subject, ChannelFunctionAction $action, array $actionParams = []) {
        $executor = $this->getExecutor($subject, $action);
        [$actionParams, $integrationsParams] = $this->groupActionParams($actionParams);
        $actionParams = $executor->validateAndTransformActionParamsFromApi($subject, $actionParams);
        $this->processIntegrationParams($subject, $integrationsParams);
        $executor->execute($subject, $actionParams);
        $this->suplaServer->clearCommandContext();
    }

    public function validateAndTransformActionParamsFromApi(
        ActionableSubject $subject,
        ChannelFunctionAction $action,
        array $actionParams
    ): array {
        try {
            $executor = $this->getExecutor($subject, $action);
        } catch (\InvalidArgumentException $e) {
        }
        return isset($executor) ? $executor->validateAndTransformActionParamsFromApi($subject, $actionParams) : [];
    }

    public function transformActionParamsForApi(ActionableSubject $subject, ChannelFunctionAction $action, array $actionParams): array {
        try {
            $executor = $this->getExecutor($subject, $action);
        } catch (\InvalidArgumentException $e) {
        }
        return isset($executor) ? $executor->transformActionParamsForApi($subject, $actionParams) : $actionParams;
    }

    private function getExecutor(ActionableSubject $subject, ChannelFunctionAction $action): SingleChannelActionExecutor {
        Assertion::keyIsset($this->actionExecutors, $action->getName(), 'Cannot execute requested action through API.');
        $executors = $this->actionExecutors[$action->getName()];
        foreach ($executors as $executor) {
            if (in_array($subject->getFunction()->getId(), EntityUtils::mapToIds($executor->getSupportedFunctions()))) {
                return $executor;
            }
        }
        Assertion::true(
            false,
            "Cannot execute the requested action {$action->getName()} on function {$subject->getFunction()->getName()}."
        );
    }

    private function groupActionParams(array $actionParams): array {
        return [
            array_diff_key($actionParams, array_flip(self::INTEGRATIONS_ACTION_PARAMS)),
            array_intersect_key($actionParams, array_flip(self::INTEGRATIONS_ACTION_PARAMS)),
        ];
    }

    private function processIntegrationParams(ActionableSubject $subject, array $integrationsParams): void {
        if (isset($integrationsParams['googleRequestId'])) {
            $googleRequestId = $integrationsParams['googleRequestId'];
            Assertion::maxLength($googleRequestId, 512, 'Google Request Id is too long.');
            if ($subject instanceof HasUserConfig) {
                $settings = $subject->getUserConfigValue('googleHome', []);
                if ($settings['googleHomeDisabled'] ?? false) {
                    throw new ApiException('Google Home has been disabled for this channel.', 409);
                }
                if ($settings['needsUserConfirmation'] ?? false) {
                    if (!($integrationsParams['googleUserConfirmation'] ?? false)) {
                        throw new ApiExceptionWithDetails(
                            'Param googleUserConfirmation=true is required for this channel.',
                            ['needsUserConfirmation' => true]
                        );
                    }
                }
                if ($settings['pin'] ?? false) {
                    if (!($integrationsParams['googlePin'] ?? false)) {
                        throw new ApiExceptionWithDetails(
                            'Param googlePin=PIN is required for this channel.',
                            ['needsPin' => true]
                        );
                    }
                    $pinStored = $settings['pin'];
                    $pinSent = $subject->getUser()->hashValue($integrationsParams['googlePin']);
                    if ($pinStored !== $pinSent) {
                        throw new ApiExceptionWithDetails(
                            'PIN sent in googlePin=PIN is invalid for this channel.',
                            ['invalidPin' => true]
                        );
                    }
                }
            }
            $this->suplaServer->setCommandContext('GOOGLE-REQUEST-ID', $googleRequestId);
        } elseif (isset($integrationsParams['alexaCorrelationToken'])) {
            $alexaCorrelationToken = $integrationsParams['alexaCorrelationToken'];
            Assertion::maxLength($alexaCorrelationToken, 2048, 'Correlation token is too long.');
            if ($subject instanceof IODeviceChannel) {
                $settings = $subject->getUserConfigValue('alexa', []);
                if ($settings['alexaDisabled'] ?? false) {
                    throw new ApiException('Alexa has been disabled for this channel.', 409);
                }
            }
            $this->suplaServer->setCommandContext('ALEXA-CORRELATION-TOKEN', $alexaCorrelationToken);
        }
    }
}
