<?php

namespace SuplaBundle\Serialization\RequestFiller;

use Assert\Assertion;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\PushNotification;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Repository\AccessIdRepository;
use SuplaBundle\Repository\ActionableSubjectRepository;
use SuplaBundle\Repository\UserRepository;

class SubjectActionFiller {
    /** @var ActionableSubjectRepository */
    private $subjectRepository;
    /** @var ChannelActionExecutor */
    private $channelActionExecutor;
    /** @var AccessIdRepository */
    private $aidRepository;
    /** @var UserRepository */
    private $userRepository;

    public function __construct(
        ActionableSubjectRepository $subjectRepository,
        ChannelActionExecutor $channelActionExecutor,
        AccessIdRepository $aidRepository,
        UserRepository $userRepository
    ) {
        $this->subjectRepository = $subjectRepository;
        $this->channelActionExecutor = $channelActionExecutor;
        $this->aidRepository = $aidRepository;
        $this->userRepository = $userRepository;
    }

    public function getSubjectAndAction(User $user, array $data): array {
        Assertion::keyExists($data, 'subjectType', 'Invalid subject type.');
        Assertion::inArray($data['subjectType'], ActionableSubjectType::toArray(), 'Invalid subject type.');
        Assertion::keyExists(
            $data,
            'actionId',
            'Missing action.' // i18n
        );
        if ($data['subjectType'] === ActionableSubjectType::NOTIFICATION) {
            return $this->createNotificationSubject($user, $data);
        } else {
            return $this->fetchExistingSubject($user, $data);
        }
    }

    private function createNotificationSubject(User $user, array $data) {
        $user = $this->userRepository->find($user->getId()); // to fetch relations count
        Assertion::false($user->isLimitNotificationsExceeded(), 'Push notifications limit has been exceeded'); // i18n
        $notification = new PushNotification($user);
        $actionParam = $data['actionParam'] ?? [] ?: [];
        $actionParam = $this->channelActionExecutor->validateAndTransformActionParamsFromApi(
            $notification,
            ChannelFunctionAction::SEND(),
            $actionParam
        );
        $notification->initFromValidatedActionParams($actionParam, $this->aidRepository);
        return [$notification, ChannelFunctionAction::SEND(), $actionParam];
    }

    private function fetchExistingSubject(User $user, array $data) {
        Assertion::keyExists($data, 'subjectId', 'You must set subjectId for reaction.');
        /** @var ActionableSubject $subject */
        $subject = $this->subjectRepository->findForUser($user, $data['subjectType'], $data['subjectId']);
        Assertion::true($subject->getFunction()->isOutput(), 'Cannot execute an action on this subject.');
        $action = ChannelFunctionAction::fromString($data['actionId']);
        $actionParam = $data['actionParam'] ?? [] ?: [];
        $actionParam = $this->channelActionExecutor->validateAndTransformActionParamsFromApi($subject, $action, $actionParam);
        Assertion::inArray(
            $action->getId(),
            EntityUtils::mapToIds($subject->getPossibleActions()),
            'Cannot execute the requested action on given subject.'
        );
        return [$subject, $action, $actionParam];
    }
}
