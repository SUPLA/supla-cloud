<?php
namespace SuplaBundle\ParamConverter;

use Assert\Assertion;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Scene;
use SuplaBundle\Entity\SceneOperation;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Repository\ActionableSubjectRepository;
use SuplaBundle\Repository\ChannelGroupRepository;
use SuplaBundle\Repository\IODeviceChannelRepository;
use SuplaBundle\Repository\LocationRepository;
use SuplaBundle\Repository\UserIconRepository;

class SceneParamConverter extends AbstractBodyParamConverter {
    use CurrentUserAware;

    /** @var IODeviceChannelRepository */
    private $channelRepository;
    /** @var ChannelGroupRepository */
    private $channelGroupRepository;
    /** @var ChannelActionExecutor */
    private $channelActionExecutor;
    /** @var LocationRepository */
    private $locationRepository;
    /** @var UserIconRepository */
    private $userIconRepository;
    /**
     * @var ActionableSubjectRepository
     */
    private $subjectRepository;

    public function __construct(
        ActionableSubjectRepository $subjectRepository,
        ChannelActionExecutor $channelActionExecutor,
        LocationRepository $locationRepository,
        UserIconRepository $userIconRepository
    ) {
        $this->subjectRepository = $subjectRepository;
        $this->channelActionExecutor = $channelActionExecutor;
        $this->locationRepository = $locationRepository;
        $this->userIconRepository = $userIconRepository;
    }

    public function getConvertedClass(): string {
        return Scene::class;
    }

    public function convert(array $data) {
        $user = $this->getCurrentUserOrThrow();
        if (isset($data['locationId']) && $data['locationId']) {
            $location = $this->locationRepository->findForUser($user, $data['locationId']);
        } else {
            $location = $user->getLocations()[0];
        }
        $scene = new Scene($location);
        $scene->setCaption($data['caption'] ?? '');
        Assertion::maxLength($scene->getCaption(), 100, 'Caption is too long.'); // i18n
        $scene->setEnabled($data['enabled'] ?? false);
        $operations = $data['operations'] ?? [];
        Assertion::isArray($operations, 'Invalid operations spec.');
        Assertion::allIsArray($operations, 'Invalid operations spec.');
        Assertion::greaterThan(count($operations), 0, 'Scene must consist of at least one operation.'); // i18n
        $operations = array_map(function (array $operationData) use ($scene, $user) {
            Assertion::keyExists($operationData, 'subjectId', 'You must set subjectId for each scene operation.');
            Assertion::keyExists($operationData, 'subjectType', 'You must set subjectType for each scene operation.');
            Assertion::keyExists(
                $operationData,
                'actionId',
                'You must set action for each scene operation.' // i18n
            );
            Assertion::inArray($operationData['subjectType'], ActionableSubjectType::toArray(), 'Invalid subject type.');
            /** @var ActionableSubject $subject */
            $subject = $this->subjectRepository->findForUser($user, $operationData['subjectType'], $operationData['subjectId']);
            Assertion::true($subject->getFunction()->isOutput(), 'Cannot execute an action on this subject.');
            $action = ChannelFunctionAction::fromString($operationData['actionId']);
            $actionParam = $operationData['actionParam'] ?? [] ?: [];
            $actionParam = $this->channelActionExecutor->validateActionParams($subject, $action, $actionParam);
            Assertion::inArray($action->getId(), EntityUtils::mapToIds($subject->getPossibleActions()));
            $delayMs = $operationData['delayMs'] ?? 0;
            return new SceneOperation($subject, $action, $actionParam, $delayMs);
        }, $operations);
        $scene->setOpeartions($operations);
        $scene->setAltIcon($data['altIcon'] ?? 0);
        if (isset($data['userIconId']) && $data['userIconId']) {
            $icon = $this->userIconRepository->findForUser($user, $data['userIconId']);
            Assertion::eq($icon->getFunction()->getId(), $scene->getFunction()->getId(), 'Chosen user icon is for other function.');
            $scene->setUserIcon($icon);
        }
        return $scene;
    }
}
