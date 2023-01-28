<?php
namespace SuplaBundle\Serialization\RequestFiller;

use Assert\Assertion;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\Scene;
use SuplaBundle\Entity\Main\SceneOperation;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Repository\ActionableSubjectRepository;
use SuplaBundle\Repository\ChannelGroupRepository;
use SuplaBundle\Repository\IODeviceChannelRepository;
use SuplaBundle\Repository\LocationRepository;
use SuplaBundle\Repository\UserIconRepository;
use SuplaBundle\Utils\SceneUtils;

class SceneRequestFiller extends AbstractRequestFiller {
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

    /**
     * @param Scene $scene
     */
    public function fillFromData(array $data, $scene = null) {
        $user = $this->getCurrentUserOrThrow();
        if (!$scene) {
            $scene = new Scene($user->getLocations()[0]);
            $data = array_merge([
                'operations' => [],
            ], $data);
        }
        if (array_key_exists('locationId', $data)) {
            Assertion::greaterThan($data['locationId'], 0);
            $location = $this->locationRepository->findForUser($user, $data['locationId']);
            $scene->setLocation($location);
        }
        if (array_key_exists('caption', $data)) {
            Assertion::string($data['caption']);
            $scene->setCaption($data['caption'] ?? '');
            Assertion::maxLength($scene->getCaption(), 100, 'Caption is too long.'); // i18n
        }
        if (array_key_exists('enabled', $data)) {
            $scene->setEnabled(boolval($data['enabled']));
        }
        if (array_key_exists('hidden', $data)) {
            $scene->setHidden(boolval($data['hidden']));
        }
        if (array_key_exists('operations', $data)) {
            $operations = $data['operations'];
            Assertion::isArray($operations, 'Invalid operations spec.');
            Assertion::allIsArray($operations, 'Invalid operations spec.');
            Assertion::greaterThan(count($operations), 0, 'Scene must consist of at least one operation.'); // i18n
            Assertion::lessOrEqualThan(
                count($operations),
                $user->getLimitOperationsPerScene(),
                'Too many operations in this scene' // i18n
            );
            $operations = array_map(function (array $operationData) use ($scene, $user) {
                Assertion::keyExists($operationData, 'subjectId', 'You must set subjectId for each scene operation.');
                Assertion::keyExists($operationData, 'subjectType', 'You must set subjectType for each scene operation.');
                Assertion::keyExists(
                    $operationData,
                    'actionId',
                    'You must set an action for each scene operation.' // i18n
                );
                Assertion::inArray($operationData['subjectType'], ActionableSubjectType::toArray(), 'Invalid subject type.');
                /** @var ActionableSubject $subject */
                $subject = $this->subjectRepository->findForUser($user, $operationData['subjectType'], $operationData['subjectId']);
                Assertion::true($subject->getFunction()->isOutput(), 'Cannot execute an action on this subject.');
                $action = ChannelFunctionAction::fromString($operationData['actionId']);
                $actionParam = $operationData['actionParam'] ?? [] ?: [];
                $actionParam = $this->channelActionExecutor->validateActionParams($subject, $action, $actionParam);
                Assertion::inArray($action->getId(), EntityUtils::mapToIds($subject->getPossibleActions()));
                $delayMs = intval($operationData['delayMs'] ?? 0);
                $waitForCompletion = boolval($operationData['waitForCompletion'] ?? false);
                return new SceneOperation($subject, $action, $actionParam, $delayMs, $waitForCompletion);
            }, $operations);
            $scene->setOpeartions($operations);
            SceneUtils::ensureOperationsAreNotCyclic($scene);
        }
        if (array_key_exists('userIconId', $data)) {
            $icon = $this->userIconRepository->findForUser($user, $data['userIconId']);
            Assertion::eq($icon->getFunction()->getId(), $scene->getFunction()->getId(), 'Chosen user icon is for other function.');
            $scene->setUserIcon($icon);
            $scene->setAltIcon(0);
        } elseif (array_key_exists('altIcon', $data)) {
            $scene->setAltIcon($data['altIcon'] ?? 0);
        }
        return $scene;
    }
}
