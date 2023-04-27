<?php
namespace SuplaBundle\Repository;

use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\ActionableSubjectType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ActionableSubjectRepository {
    /** @var IODeviceChannelRepository */
    private $channelRepository;
    /** @var ChannelGroupRepository */
    private $channelGroupRepository;
    /** @var SceneRepository */
    private $sceneRepository;
    /** @var ScheduleRepository */
    private $scheduleRepository;

    public function __construct(
        IODeviceChannelRepository $channelRepository,
        ChannelGroupRepository $channelGroupRepository,
        SceneRepository $sceneRepository,
        ScheduleRepository $scheduleRepository
    ) {
        $this->channelRepository = $channelRepository;
        $this->channelGroupRepository = $channelGroupRepository;
        $this->sceneRepository = $sceneRepository;
        $this->scheduleRepository = $scheduleRepository;
    }

    /**
     * Finds subject by type and id that belongs to the given user.
     * @param \SuplaBundle\Entity\Main\User $user user that should own the channel
     * @param string|ActionableSubjectType $type type of the subject to return
     * @param int $id id of the subject to return
     * @return ActionableSubject found subject
     * @throws NotFoundHttpException if the subject does not exist or does not belong to the given user
     */
    public function findForUser(User $user, $type, int $id): ActionableSubject {
        $subjectType = $type instanceof ActionableSubjectType ? $type : ActionableSubjectType::fromString($type);
        switch ($subjectType->getValue()) {
            case ActionableSubjectType::CHANNEL:
                return $this->channelRepository->findForUser($user, $id);
            case ActionableSubjectType::CHANNEL_GROUP:
                return $this->channelGroupRepository->findForUser($user, $id);
            case ActionableSubjectType::SCENE:
                return $this->sceneRepository->findForUser($user, $id);
            case ActionableSubjectType::SCHEDULE:
                return $this->scheduleRepository->findForUser($user, $id);
            default:
                throw new \InvalidArgumentException('No repository configured for ' . $subjectType);
        }
    }
}
