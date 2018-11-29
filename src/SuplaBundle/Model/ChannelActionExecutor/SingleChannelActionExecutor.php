<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Entity\HasFunction;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Supla\SuplaServerAware;

abstract class SingleChannelActionExecutor {
    use SuplaServerAware;

    /** @return ChannelFunction[] */
    abstract public function getSupportedFunctions(): array;

    abstract public function getSupportedAction(): ChannelFunctionAction;

    abstract public function execute(HasFunction $subject, array $actionParams = []);

    public function assignCommonParams(array $source, array $actionParams = []) {
        if (array_key_exists('alexaCorrelationToken', $actionParams)) {
            $source['ACT'] = 'ALEXA-CORRELATION-TOKEN='.base64_encode($actionParams['alexaCorrelationToken']);
        }

        return $source;
    }

    public function validateActionParams(HasFunction $subject, array $actionParams): array {
        if (array_key_exists('alexaCorrelationToken', $actionParams)) {
            Assertion::eq(
                count($actionParams),
                1,
                'This action is not supposed to have more parameters than one. (alexaCorrelationToken)'
            );
            Assertion::lessOrEqualThan(strlen($actionParams['alexaCorrelationToken']), 2048, 'Correlation token is too long.');
        } else {
            Assertion::noContent($actionParams, 'This action is not supposed to have any parameters.');
        }

        return $actionParams;
    }
}
