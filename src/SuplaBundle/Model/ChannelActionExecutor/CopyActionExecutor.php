<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Repository\ActionableSubjectRepository;

/**
 * @OA\Schema(schema="ChannelActionParamsCopy",
 *     description="Action params for `COPY` action.",
 *     @OA\Property(property="sourceChannelId", type="integer"),
 * )
 */
class CopyActionExecutor extends SingleChannelActionExecutor {
    /** @var ActionableSubjectRepository */
    private $subjectRepository;

    public function __construct(ActionableSubjectRepository $subjectRepository) {
        $this->subjectRepository = $subjectRepository;
    }

    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::CONTROLLINGTHEGARAGEDOOR(),
            ChannelFunction::CONTROLLINGTHEGATE(),
            ChannelFunction::CONTROLLINGTHEROLLERSHUTTER(),
            ChannelFunction::CONTROLLINGTHEROOFWINDOW(),
            ChannelFunction::TERRACE_AWNING(),
            ChannelFunction::PROJECTOR_SCREEN(),
            ChannelFunction::CURTAIN(),
            ChannelFunction::ROLLER_GARAGE_DOOR(),
            ChannelFunction::CONTROLLINGTHEFACADEBLIND(),
            ChannelFunction::VERTICAL_BLIND(),
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

    public function validateAndTransformActionParamsFromApi(ActionableSubject $subject, array $actionParams): array {
        $params = array_intersect_key($actionParams, ['sourceChannelId' => '']);
        Assertion::count($params, 1, 'Choose the source channel.'); // i18n
        $sourceChannelId = $params['sourceChannelId'];
        Assertion::greaterThan($sourceChannelId, 0, 'Choose the source channel.'); // i18n
        if ($subject->getOwnSubjectType() === ActionableSubjectType::CHANNEL) {
            Assertion::notEq($sourceChannelId, $subject->getId(), 'Source and target channel must be different.');
        }
        $sourceChannel = $this->subjectRepository
            ->findForUser($subject->getUser(), ActionableSubjectType::CHANNEL, $sourceChannelId);
        Assertion::notNull($sourceChannel, 'Invalid source channel.');
        Assertion::eq(
            $subject->getFunction()->getId(),
            $sourceChannel->getFunction()->getId(),
            'Source channel must have the same function.'
        );
        return ['sourceDeviceId' => $sourceChannel->getIoDevice()->getId(), 'sourceChannelId' => $sourceChannel->getId()];
    }

    public function execute(ActionableSubject $subject, array $actionParams = []) {
        /** @var \SuplaBundle\Entity\Main\IODeviceChannel $sourceChannel */
        $sourceChannel = $this->subjectRepository
            ->findForUser($subject->getUser(), ActionableSubjectType::CHANNEL, $actionParams['sourceChannelId']);
        $command = $subject->buildServerActionCommand('ACTION-COPY', [$sourceChannel->getIoDevice()->getId(), $sourceChannel->getId()]);
        $this->suplaServer->executeCommand($command);
    }
}
