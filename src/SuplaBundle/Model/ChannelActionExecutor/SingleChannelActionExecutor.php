<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Supla\SuplaServerAware;

abstract class SingleChannelActionExecutor {
    use SuplaServerAware;

    /** @return ChannelFunction[] */
    abstract public function getSupportedFunctions(): array;

    abstract public function getSupportedAction(): ChannelFunctionAction;

    abstract public function execute(ActionableSubject $subject, array $actionParams = []);

    public function assignCommonParams(array $source, array $actionParams = []) {

        if (array_key_exists('alexaCorrelationToken', $actionParams)) {
            $source['ACT'] = 'ALEXA-CORRELATION-TOKEN=' . base64_encode($actionParams['alexaCorrelationToken']);
        }
        if (array_key_exists('googleRequestId', $actionParams)) {
            $source['GRI'] = 'GOOGLE-REQUEST-ID=' . base64_encode($actionParams['googleRequestId']);
        }

        return $source;
    }

    public function validateActionParams(ActionableSubject $subject, array $actionParams): array {
        if (count(array_intersect_key($actionParams, array_flip(['alexaCorrelationToken', 'googleRequestId']))) > 0) {
            Assertion::eq(
                count($actionParams),
                1,
                'This action is not supposed to have more parameters than one. (alexaCorrelationToken/googleRequestId)'
            );
            Assertion::lessOrEqualThan(strlen($actionParams['alexaCorrelationToken'] ?? ''), 2048, 'Correlation token is too long.');
            Assertion::lessOrEqualThan(strlen($actionParams['googleRequestId'] ?? ''), 512, 'Google Request Id is too long.');
        } else {
            Assertion::noContent($actionParams, 'This action is not supposed to have any parameters.');
        }
        return $actionParams;
    }
}
