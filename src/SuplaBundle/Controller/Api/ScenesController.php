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
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Auth\Voter\AccessIdSecurityVoter;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\IODeviceChannelGroup;
use SuplaBundle\Entity\Main\Scene;
use SuplaBundle\Entity\Main\SceneOperation;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Model\Dependencies\SceneDependencies;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Repository\SceneRepository;
use SuplaBundle\Serialization\RequestFiller\SceneRequestFiller;
use SuplaBundle\Utils\SceneUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @OA\Schema(
 *   schema="SceneOperation", type="object",
 *   @OA\Property(property="actionId", ref="#/components/schemas/ChannelFunctionActionIds"),
 *   @OA\Property(property="actionParam", nullable=true, ref="#/components/schemas/ChannelActionParams"),
 *   @OA\Property(property="delayMs", type="integer", description="Delay before this operation in scene, in milliseconds."),
 *   @OA\Property(property="waitForCompletion", type="boolean", description="Whether to wait before proceeding with the scene until this operation completes."),
 *   @OA\Property(property="subjectType", ref="#/components/schemas/ActionableSubjectTypeNames"),
 *   @OA\Property(property="subjectId", type="integer"),
 *   @OA\Property(property="owningSceneId", description="ID of the scene that this operation belongs to.", type="integer"),
 *   @OA\Property(property="subject", description="Only if requested by the `include` param.", ref="#/components/schemas/ActionableSubject"),
 * )
 * @OA\Schema(
 *   schema="Scene", type="object",
 *   @OA\Property(property="id", type="integer", description="Identifier"),
 *   @OA\Property(property="caption", type="string", description="Caption"),
 *   @OA\Property(property="altIcon", type="integer", description="Chosen alternative icon idenifier. Should not be greater than the `function.maxAlternativeIconIndex`."),
 *   @OA\Property(property="enabled", type="boolean", description="Whether this scene is enabled or not"),
 *   @OA\Property(property="hidden", type="boolean", description="Whether this scene is shown on client apps or not"),
 *   @OA\Property(property="estimatedExecutionTime", type="integer", description="Estimated execution time for this scene (in milliseconds)."),
 *   @OA\Property(property="ownSubjectType", type="string", enum={"scene"}),
 *   @OA\Property(property="possibleActions", type="array", description="What action can you execute on this subject?", @OA\Items(ref="#/components/schemas/ChannelFunctionAction")),
 *   @OA\Property(property="function", ref="#/components/schemas/ChannelFunction"),
 *   @OA\Property(property="operations", description="Scene operations, only if requested in the `include` param", type="array", @OA\Items(ref="#/components/schemas/SceneOperation")),
 *   @OA\Property(property="location", description="Channel location, if requested by the `include` param", ref="#/components/schemas/Location"),
 *   @OA\Property(property="locationId", type="integer"),
 *   @OA\Property(property="functionId", type="integer", enum={2000}),
 *   @OA\Property(property="userIconId", type="integer"),
 *   @OA\Property(property="config", ref="#/components/schemas/ChannelConfig"),
 *   @OA\Property(property="state", ref="#/components/schemas/SceneState"),
 *   @OA\Property(property="relationsCount", description="Counts of related entities.",
 *     @OA\Property(property="sceneOperations", description="Number of scene operations, that this scene is used for an action.", type="integer"),
 *     @OA\Property(property="directLinks", type="integer"),
 *     @OA\Property(property="schedules", type="integer"),
 *     @OA\Property(property="scenes", description="Number of scenes that this scene is used for an action.", type="integer"),
 *     @OA\Property(property="operations", description="Number of scene operations that this scene has.", type="integer"),
 *   ),
 * )
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
            'location', 'state',
            'location' => 'scene.location',
            'state' => 'scene.state',
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

    private function returnScenes(callable $additionalConditions = null): Collection {
        return $this->sceneRepository->findAllForUser($this->getUser(), $additionalConditions)
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

    private function ensureApiVersion24(Request $request) {
        if (!ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($request)) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @OA\Get(
     *     path="/scenes", operationId="getScenes", summary="Get Scenes", tags={"Scenes"},
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"location", "state"})),
     *     ),
     *     @OA\Parameter(
     *         description="Select an integration that the scenes should be returned for.",
     *         in="query", name="forIntegration", required=false,
     *         @OA\Schema(type="string", enum={"google-home", "alexa"}),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Scene"))),
     * )
     * @Rest\Get("/scenes", name="scenes_list")
     * @Security("is_granted('ROLE_SCENES_R')")
     */
    public function getScenesAction(Request $request) {
        $this->ensureApiVersion24($request);
        $filters = function (QueryBuilder $builder, string $alias) use ($request) {
            if (($forIntegration = $request->get('forIntegration')) !== null) {
                if ($forIntegration === 'google-home') {
                    $builder->andWhere("$alias.userConfig IS NULL OR $alias.userConfig NOT LIKE :googleHomeFilter")
                        ->setParameter('googleHomeFilter', '%"googleHomeDisabled":true%');
                } elseif ($forIntegration === 'alexa') {
                    $builder->andWhere("$alias.userConfig IS NULL OR $alias.userConfig NOT LIKE :alexaFilter")
                        ->setParameter('alexaFilter', '%"alexaDisabled":true%');
                }
            }
        };
        return $this->serializedView($this->returnScenes($filters)->getValues(), $request);
    }

    /**
     * @OA\Get(
     *     path="/channels/{channel}/scenes", operationId="getChannelScenes", summary="Get channel scenes", tags={"Channels"},
     *     @OA\Parameter(description="ID", in="path", name="channel", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"location", "state"})),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Scene"))),
     * )
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_R')")
     * @Rest\Get("/channels/{channel}/scenes")
     */
    public function getChannelScenesAction(IODeviceChannel $channel, Request $request) {
        $this->ensureApiVersion24($request);
        $scenes = $this->returnScenesFilteredBySubject($channel);
        return $this->serializedView($scenes->getValues(), $request);
    }

    /**
     * @OA\Get(
     *     path="/channel-groups/{channelGroup}/scenes", operationId="getChannelGroupScenes", summary="Get channel group scenes", tags={"Channel Groups"},
     *     @OA\Parameter(description="ID", in="path", name="channelGroup", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"location", "state"})),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Scene"))),
     * )
     * @Security("channelGroup.belongsToUser(user) and is_granted('ROLE_CHANNELGROUPS_R')")
     * @Rest\Get("/channel-groups/{channelGroup}/scenes")
     */
    public function getChannelGroupScenesAction(IODeviceChannelGroup $channelGroup, Request $request) {
        $this->ensureApiVersion24($request);
        $directLinks = $this->returnScenesFilteredBySubject($channelGroup);
        return $this->serializedView($directLinks->getValues(), $request);
    }

    /**
     * @OA\Get(
     *     path="/scenes/{scene}/scenes", operationId="getSceneScenes", summary="Get scene scenes", tags={"Scenes"},
     *     @OA\Parameter(description="ID", in="path", name="scene", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"location", "state"})),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Scene"))),
     * )
     * @Security("scene.belongsToUser(user) and is_granted('ROLE_SCENES_R')")
     * @Rest\Get("/scenes/{scene}/scenes")
     */
    public function getSceneScenesAction(Scene $scene, Request $request) {
        $this->ensureApiVersion24($request);
        $directLinks = $this->returnScenesFilteredBySubject($scene);
        return $this->serializedView($directLinks->getValues(), $request);
    }

    /**
     * @OA\Get(
     *     path="/scenes/{scene}", operationId="getScene", summary="Get Scene", tags={"Scenes"},
     *     @OA\Parameter(description="ID", in="path", name="scene", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"location", "state", "subject", "operations"})),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/Scene")),
     * )
     * @Rest\Get("/scenes/{scene}")
     * @Security("scene.belongsToUser(user) and is_granted('ROLE_SCENES_R')")
     */
    public function getSceneAction(Request $request, Scene $scene) {
        $this->ensureApiVersion24($request);
        return $this->serializedView($scene, $request, ['scene.relationsCount', 'location.relationsCount']);
    }

    /**
     * @OA\Post(
     *     path="/scenes", operationId="createScene", summary="Create a scene", tags={"Scenes"},
     *     @OA\RequestBody(
     *       required=true,
     *       @OA\JsonContent(
     *         @OA\Property(property="enabled", type="boolean"),
     *         @OA\Property(property="hidden", type="boolean"),
     *         @OA\Property(property="caption", type="string"),
     *         @OA\Property(property="locationId", type="integer"),
     *         @OA\Property(property="altIcon", type="integer"),
     *         @OA\Property(property="userIconId", type="integer"),
     *         @OA\Property(property="operations", type="array", @OA\Items(
     *           @OA\Property(property="subjectId", type="integer"),
     *           @OA\Property(property="subjectType", ref="#/components/schemas/ActionableSubjectTypeNames"),
     *           @OA\Property(property="delayMs", type="integer"),
     *           @OA\Property(property="waitForCompletion", type="boolean"),
     *           @OA\Property(property="actionId", ref="#/components/schemas/ChannelFunctionActionIds"),
     *           @OA\Property(property="actionParam", nullable=true, ref="#/components/schemas/ChannelActionParams"),
     *         )),
     *       )
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/Scene")),
     * )
     * @Rest\Post("/scenes")
     * @Security("is_granted('ROLE_SCENES_RW')")
     */
    public function postSceneAction(Request $request, SceneRequestFiller $sceneFiller, TranslatorInterface $translator) {
        $this->ensureApiVersion24($request);
        $scene = $this->transactional(function (EntityManagerInterface $em) use ($translator, $request, $sceneFiller) {
            $user = $this->getUser();
            $scene = $sceneFiller->fillFromRequest($request);
            if (!$scene->getCaption()) {
                $caption = $translator->trans('Scene', [], null, $user->getLocale()); // i18n
                $scene->setCaption($caption . ' #' . ($user->getScenes()->count() + 1));
            }
            Assertion::false($user->isLimitSceneExceeded(), 'Scenes limit has been exceeded'); // i18n
            $em->persist($scene);
            SceneUtils::updateDelaysAndEstimatedExecutionTimes($scene, $em);
            return $scene;
        });
        return $this->serializedView($scene, $request, ['scene.relationsCount'], Response::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *     path="/scenes/{scene}", operationId="updateScene", summary="Update the scene", tags={"Scenes"},
     *     @OA\Parameter(description="ID", in="path", name="scene", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *       required=true,
     *       @OA\JsonContent(
     *         @OA\Property(property="enabled", type="boolean"),
     *         @OA\Property(property="hidden", type="boolean"),
     *         @OA\Property(property="caption", type="string"),
     *         @OA\Property(property="locationId", type="integer"),
     *         @OA\Property(property="altIcon", type="integer"),
     *         @OA\Property(property="userIconId", type="integer"),
     *         @OA\Property(property="operations", type="array", @OA\Items(
     *           @OA\Property(property="subjectId", type="integer"),
     *           @OA\Property(property="subjectType", ref="#/components/schemas/ActionableSubjectTypeNames"),
     *           @OA\Property(property="delayMs", type="integer"),
     *           @OA\Property(property="waitForCompletion", type="boolean"),
     *           @OA\Property(property="actionId", ref="#/components/schemas/ChannelFunctionActionIds"),
     *           @OA\Property(property="actionParam", nullable=true, ref="#/components/schemas/ChannelActionParams"),
     *         )),
     *       )
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/Scene")),
     * )
     * @Rest\Put("/scenes/{scene}")
     * @Security("scene.belongsToUser(user) and is_granted('ROLE_SCENES_RW')")
     */
    public function putSceneAction(Scene $scene, SceneRequestFiller $sceneFiller, Request $request) {
        $this->ensureApiVersion24($request);
        $sceneResponse = $this->transactional(function (EntityManagerInterface $em) use ($sceneFiller, $request, $scene) {
            $scene->getOperations()->forAll(function (int $index, SceneOperation $sceneOperation) use ($em) {
                $em->remove($sceneOperation);
                return true;
            });
            $scene = $sceneFiller->fillFromRequest($request, $scene);
            $em->persist($scene);
            SceneUtils::updateDelaysAndEstimatedExecutionTimes($scene, $em);
            return $this->getSceneAction($request, $scene);
        });
        return $sceneResponse;
    }

    /**
     * @OA\Delete(
     *     path="/scenes/{scene}", operationId="deleteScene", summary="Delete the scene", tags={"Scenes"},
     *     @OA\Parameter(description="ID", in="path", name="scene", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="204", description="Success"),
     * )
     * @Rest\Delete("/scenes/{scene}")
     * @Security("scene.belongsToUser(user) and is_granted('ROLE_SCENES_RW')")
     */
    public function deleteSceneAction(Scene $scene, Request $request, SceneDependencies $sceneDependencies) {
        $this->ensureApiVersion24($request);
        $shouldConfirm = filter_var($request->get('safe', false), FILTER_VALIDATE_BOOLEAN);
        if ($shouldConfirm) {
            $dependencies = $sceneDependencies->getDependencies($scene);
            if (array_filter($dependencies)) {
                $view = $this->view(['conflictOn' => 'deletion', 'dependencies' => $dependencies], Response::HTTP_CONFLICT);
                $this->setSerializationGroups($view, $request, ['scene'], ['scene']);
                return $view;
            }
        }
        $this->transactional(function (EntityManagerInterface $em) use ($sceneDependencies, $scene) {
            $sceneDependencies->clearDependencies($scene);
            $em->remove($scene);
        });
        return new Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @OA\Patch(
     *     path="/scenes/{scene}", operationId="executeScene", tags={"Scenes"},
     *     @OA\Parameter(description="ID", in="path", name="scene", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *       required=true,
     *       @OA\JsonContent(
     *          description="Defines an action to execute on scene. The `action` key is always required. The rest of the keys are params depending on the chosen action.",
     *          externalDocs={"description": "Github Wiki", "url":"https://github.com/SUPLA/supla-cloud/wiki/Channel-Actions"},
     *          allOf={
     *            @OA\Schema(@OA\Property(property="action", ref="#/components/schemas/ChannelFunctionActionEnumNames")),
     *            @OA\Schema(ref="#/components/schemas/ChannelActionParams"),
     *         }
     *       ),
     *     ),
     *     @OA\Response(response="202", description="Action has been committed."),
     *     @OA\Response(response="400", description="Invalid request", @OA\JsonContent(
     *          @OA\Property(property="status", type="integer", example="400"),
     *          @OA\Property(property="message", type="string", example="Cannot execute requested action."),
     *     )),
     * )
     * @Rest\Patch("/scenes/{scene}")
     * @Security("scene.belongsToUser(user) and is_granted('ROLE_SCENES_EA') and is_granted('accessIdContains', scene)")
     */
    public function patchSceneAction(Request $request, Scene $scene, ChannelActionExecutor $channelActionExecutor) {
        $this->ensureApiVersion24($request);
        $params = json_decode($request->getContent(), true);
        Assertion::keyExists($params, 'action', 'Missing action.');
        $action = ChannelFunctionAction::fromString($params['action']);
        unset($params['action']);
        $channelActionExecutor->executeAction($scene, $action, $params);
        return $this->handleView($this->view(null, Response::HTTP_ACCEPTED));
    }
}
