<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace SuplaBundle\Controller\Api;

use Assert\Assertion;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Auth\Voter\AccessIdSecurityVoter;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\IODeviceChannelGroup;
use SuplaBundle\Entity\Scene;
use SuplaBundle\Entity\SceneOperation;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Repository\SceneRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Rest\Version("2.4.0")
 */
class ScenesController extends RestController {
    use Transactional;

    /** @var SceneRepository */
    private $sceneRepository;

    public function __construct(SceneRepository $sceneRepository) {
        $this->sceneRepository = $sceneRepository;
    }

    protected function getDefaultAllowedSerializationGroups(Request $request): array {
        $groups = [
            'location',
            'location' => 'scene.location',
        ];
        if (!strpos($request->get('_route'), 'scenes_list')) {
            $groups = array_merge($groups, [
                'subject', 'operations',
                'subject' => 'sceneOperation.subject',
                'operations' => 'scene.operations',
            ]);
        }
        return $groups;
    }

    private function returnScenes(): Collection {
        return $this->sceneRepository->findAllForUser($this->getUser())
            ->filter(function (Scene $scene) {
                return $this->isGranted(AccessIdSecurityVoter::PERMISSION_NAME, $scene);
            });
    }

    private function returnScenesFilteredBySubject(ActionableSubject $subject): Collection {
        $type = ActionableSubjectType::forEntity($subject);
        return $this->returnScenes()->filter(function (Scene $scene) use ($subject, $type) {
            return $scene->getOperations()->exists(function ($index, SceneOperation $sceneOperation) use ($subject, $type) {
                return $sceneOperation->getSubjectType() == $type && $sceneOperation->getSubject()->getId() == $subject->getId();
            });
        });
    }

    /**
     * @Rest\Get("/scenes", name="scenes_list")
     * @Security("has_role('ROLE_SCENES_R')")
     */
    public function getScenesAction(Request $request) {
        return $this->serializedView($this->returnScenes()->getValues(), $request);
    }

    /**
     * @Security("channel.belongsToUser(user) and has_role('ROLE_CHANNELS_R')")
     * @Rest\Get("/channels/{channel}/scenes")
     */
    public function getChannelScenesAction(IODeviceChannel $channel, Request $request) {
        $scenes = $this->returnScenesFilteredBySubject($channel);
        return $this->serializedView($scenes->getValues(), $request);
    }

    /**
     * @Security("channelGroup.belongsToUser(user) and has_role('ROLE_CHANNELGROUPS_R')")
     * @Rest\Get("/channel-groups/{channelGroup}/scenes")
     */
    public function getChannelGroupScenesAction(IODeviceChannelGroup $channelGroup, Request $request) {
        $directLinks = $this->returnScenesFilteredBySubject($channelGroup);
        return $this->serializedView($directLinks->getValues(), $request);
    }

    /**
     * @Security("scene.belongsToUser(user) and has_role('ROLE_SCENES_R')")
     * @Rest\Get("/scenes/{scene}/scenes")
     */
    public function getSceneScenesAction(Scene $scene, Request $request) {
        $directLinks = $this->returnScenesFilteredBySubject($scene);
        return $this->serializedView($directLinks->getValues(), $request);
    }

    /**
     * @Rest\Get("/scenes/{scene}")
     * @Security("scene.belongsToUser(user) and has_role('ROLE_SCENES_R')")
     */
    public function getSceneAction(Request $request, Scene $scene) {
        return $this->serializedView($scene, $request, ['scene.relationsCount']);
    }

    /**
     * @Rest\Post("/scenes")
     * @Security("has_role('ROLE_SCENES_RW')")
     */
    public function postSceneAction(Request $request, Scene $scene, TranslatorInterface $translator) {
        $user = $this->getUser();
        if (!$scene->getCaption()) {
            $caption = $translator->trans('Scene', [], null, $user->getLocale()); // i18n
            $scene->setCaption($caption . ' #' . ($user->getScenes()->count() + 1));
        }
        Assertion::false($user->isLimitSceneExceeded(), 'Scenes limit has been exceeded'); // i18n
        $scene->ensureOperationsAreNotCyclic();
        $scene = $this->transactional(function (EntityManagerInterface $em) use ($scene) {
            $em->persist($scene);
            return $scene;
        });
        return $this->serializedView($scene, $request, ['scene.relationsCount'], Response::HTTP_CREATED);
    }

    /**
     * @Rest\Put("/scenes/{scene}")
     * @Security("scene.belongsToUser(user) and has_role('ROLE_SCENES_RW')")
     */
    public function putSceneAction(Scene $scene, Scene $updated, Request $request) {
        return $this->transactional(function (EntityManagerInterface $em) use ($request, $scene, $updated) {
            $scene->setCaption($updated->getCaption());
            $scene->setEnabled($updated->isEnabled());
            $scene->setLocation($updated->getLocation());
            $scene->setUserIcon($updated->getUserIcon());
            $scene->getOperations()->forAll(function (int $index, SceneOperation $sceneOperation) use ($em) {
                $em->remove($sceneOperation);
                return true;
            });
            $scene->setOpeartions($updated->getOperations());
            $scene->ensureOperationsAreNotCyclic();
            $em->persist($scene);
            return $this->getSceneAction($request, $scene);
        });
    }

    /**
     * @Rest\Delete("/scenes/{scene}")
     * @Security("scene.belongsToUser(user) and has_role('ROLE_SCENES_RW')")
     */
    public function deleteSceneAction(Scene $scene) {
        return $this->transactional(function (EntityManagerInterface $em) use ($scene) {
            $em->remove($scene);
            return new Response('', Response::HTTP_NO_CONTENT);
        });
    }

    /**
     * @Rest\Patch("/scenes/{scene}")
     * @Security("scene.belongsToUser(user) and has_role('ROLE_SCENES_EA') and is_granted('accessIdContains', scene)")
     */
    public function patchSceneAction(Request $request, Scene $scene, ChannelActionExecutor $channelActionExecutor) {
        $params = json_decode($request->getContent(), true);
        Assertion::keyExists($params, 'action', 'Missing action.');
        $action = ChannelFunctionAction::fromString($params['action']);
        unset($params['action']);
        $channelActionExecutor->executeAction($scene, $action, $params);
        return $this->handleView($this->view(null, Response::HTTP_ACCEPTED));
    }
}
