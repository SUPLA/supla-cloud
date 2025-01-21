<?php
namespace SuplaBundle\Serialization\RequestFiller;

use Assert\Assertion;
use SuplaBundle\Entity\Main\Scene;
use SuplaBundle\Entity\Main\SceneOperation;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
use SuplaBundle\Repository\ChannelGroupRepository;
use SuplaBundle\Repository\IODeviceChannelRepository;
use SuplaBundle\Repository\LocationRepository;
use SuplaBundle\Repository\UserIconRepository;
use SuplaBundle\Utils\SceneUtils;

class SceneRequestFiller extends AbstractRequestFiller {
    use CurrentUserAware;
    use UserAltIconRequestFiller;
    use ActivityConditionsRequestFiller;

    public function __construct(
        private readonly LocationRepository $locationRepository,
        private readonly UserIconRepository $userIconRepository,
        private readonly SubjectConfigTranslator $configTranslator,
        private readonly SubjectActionFiller $subjectActionFiller
    ) {
    }

    /** @param Scene $scene */
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
                if ($operationData['subjectType'] ?? false) {
                    Assertion::inArray($operationData['subjectType'], ActionableSubjectType::toArray(), 'Invalid subject type.');
                    Assertion::keyExists(
                        $operationData,
                        'actionId',
                        'You must set an action for each scene operation.' // i18n
                    );
                    [$subject, $action, $actionParam] = $this->subjectActionFiller->getSubjectAndAction($user, $operationData);
                    $waitForCompletion = boolval($operationData['waitForCompletion'] ?? false);
                    $delayMs = intval($operationData['delayMs'] ?? 0);
                    return new SceneOperation($subject, $action, $actionParam, $delayMs, $waitForCompletion);
                } else {
                    $operationData['actionId'] = ChannelFunctionAction::VOID;
                    $delayMs = intval($operationData['delayMs'] ?? 0);
                    Assertion::greaterThan($delayMs, 0, 'You have to set the delay for delay-only scene operation.');
                    return SceneOperation::delayOnly($delayMs);
                }
            }, $operations);
            $scene->setOpeartions($operations);
            SceneUtils::ensureOperationsAreNotCyclic($scene);
        }
        $this->fillUserAltIcon($this->userIconRepository, $data, $scene);
        if (array_key_exists('config', $data)) {
            Assertion::isArray($data['config']);
            $this->configTranslator->setConfig($scene, $data['config']);
        }
        $this->fillActivityConditions($data, $scene);
        return $scene;
    }
}
