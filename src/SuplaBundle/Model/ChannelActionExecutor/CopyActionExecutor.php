<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Repository\ActionableSubjectRepository;

class CopyActionExecutor extends SingleChannelActionExecutor {
    /** @var ActionableSubjectRepository */
    private $subjectRepository;

    public function __construct(ActionableSubjectRepository $subjectRepository) {
        $this->subjectRepository = $subjectRepository;
    }

    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::POWERSWITCH(),
            ChannelFunction::LIGHTSWITCH(),
            ChannelFunction::STAIRCASETIMER(),
            ChannelFunction::DIMMER(),
            ChannelFunction::RGBLIGHTING(),
            ChannelFunction::DIMMERANDRGBLIGHTING(),
        ];
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::COPY();
    }

    public function validateActionParams(ActionableSubject $subject, array $actionParams): array {
        $params = array_intersect_key($actionParams, ['sourceChannelId' => '']);
        Assertion::count($params, 1, 'Invalid copy state definition.');
        if ($subject->getSubjectType() === ActionableSubjectType::CHANNEL) {
            Assertion::notEq($params['sourceChannelId'], $subject->getId(), 'Source and target channel must be different.');
        }
        $sourceChannel = $this->subjectRepository
            ->findForUser($subject->getUser(), ActionableSubjectType::CHANNEL, $params['sourceChannelId']);
        Assertion::notNull($sourceChannel, 'Invalid source channel.');
        Assertion::eq($subject->getFunction()->getId(), $sourceChannel->getFunction()->getId(), 'Source channel must have the same function.');
        return $params;
    }

    public function execute(ActionableSubject $subject, array $actionParams = []) {
        /** @var IODeviceChannel $sourceChannel */
        $sourceChannel = $this->subjectRepository
            ->findForUser($subject->getUser(), ActionableSubjectType::CHANNEL, $actionParams['sourceChannelId']);
        $command = $subject->buildServerActionCommand('ACTION-COPY', [$sourceChannel->getIoDevice()->getId(), $sourceChannel->getId()]);
        $this->suplaServer->executeCommand($command);
    }
}
