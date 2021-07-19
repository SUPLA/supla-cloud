<?php
namespace SuplaBundle\Repository;

use SuplaBundle\Entity\HasFunction;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ActionableSubjectType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ActionableSubjectRepository {
    /** @var IODeviceChannelRepository */
    private $channelRepository;
    /** @var ChannelGroupRepository */
    private $channelGroupRepository;
    /** @var SceneRepository */
    private $sceneRepository;

    public function __construct(
        IODeviceChannelRepository $channelRepository,
        ChannelGroupRepository $channelGroupRepository,
        SceneRepository $sceneRepository
    ) {
        $this->channelRepository = $channelRepository;
        $this->channelGroupRepository = $channelGroupRepository;
        $this->sceneRepository = $sceneRepository;
    }

    /**
     * Finds subject by type and id that belongs to the given user.
     * @param User $user user that should own the channel
     * @param string|ActionableSubjectType $type type of the subject to return
     * @param int $id id of the subject to return
     * @return HasFunction found subject
     * @throws NotFoundHttpException if the subject does not exist or does not belong to the given user
     */
    public function findForUser(User $user, $type, int $id): HasFunction {
        $subjectType = $type instanceof ActionableSubjectType ? $type : ActionableSubjectType::fromString($type);
        switch ($subjectType->getValue()) {
            case ActionableSubjectType::CHANNEL:
                return $this->channelRepository->findForUser($user, $id);
            case ActionableSubjectType::CHANNEL_GROUP:
                return $this->channelGroupRepository->findForUser($user, $id);
            case ActionableSubjectType::SCENE:
                return $this->sceneRepository->findForUser($user, $id);
        }
    }
}
