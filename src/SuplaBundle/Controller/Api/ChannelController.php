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
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Auth\Voter\AccessIdSecurityVoter;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\EventListener\UnavailableInMaintenance;
use SuplaBundle\Exception\ApiException;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Model\ChannelStateGetter\ChannelStateGetter;
use SuplaBundle\Model\Dependencies\ChannelDependencies;
use SuplaBundle\Model\Schedule\ScheduleManager;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
use SuplaBundle\Repository\IODeviceChannelRepository;
use SuplaBundle\Repository\LocationRepository;
use SuplaBundle\Repository\UserIconRepository;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Schema(
 *   schema="Channel", type="object",
 *   @OA\Property(property="id", type="integer", description="Identifier"),
 *   @OA\Property(property="channelNumber", type="integer", description="Channel ordinal number in its IO Device"),
 *   @OA\Property(property="caption", type="string", description="Caption"),
 *   @OA\Property(property="altIcon", type="integer", description="Chosen alternative icon idenifier. Should not be greater than the `function.maxAlternativeIconIndex`."),
 *   @OA\Property(property="hidden", type="boolean", description="Whether this channel is shown on client apps or not"),
 *   @OA\Property(property="inheritedLocation", type="boolean", description="Whether this channel inherits its IO Device's location (`true`) or not (`false`)"),
 *   @OA\Property(property="iodeviceId", type="integer"),
 *   @OA\Property(property="iodevice", ref="#/components/schemas/Device", description="Only if requested by the `include` param."),
 *   @OA\Property(property="locationId", type="integer"),
 *   @OA\Property(property="location", nullable=true, description="Channel location, if requested by the `include` param", ref="#/components/schemas/Location"),
 *   @OA\Property(property="functionId", type="integer", example=60),
 *   @OA\Property(property="function", ref="#/components/schemas/ChannelFunction"),
 *   @OA\Property(property="possibleActions", type="array", description="What action can you execute on this subject?", @OA\Items(ref="#/components/schemas/ChannelFunctionAction")),
 *   @OA\Property(property="typeId", type="integer", example=1000),
 *   @OA\Property(property="type", ref="#/components/schemas/ChannelType"),
 *   @OA\Property(property="ownSubjectType", type="string", enum={"channel"}),
 *   @OA\Property(property="state", ref="#/components/schemas/ChannelState"),
 *   @OA\Property(property="config", ref="#/components/schemas/ChannelConfig"),
 *   @OA\Property(property="userIconId", type="integer"),
 *   @OA\Property(property="userIcon", ref="#/components/schemas/UserIcon", description="User Icon, if requested by the `include` param"),
 *   @OA\Property(property="connected", type="boolean"),
 *   @OA\Property(property="relationsCount", description="Counts of related entities.",
 *     @OA\Property(property="channelGroups", type="integer"),
 *     @OA\Property(property="directLinks", type="integer"),
 *     @OA\Property(property="schedules", type="integer"),
 *     @OA\Property(property="scenes", type="integer"),
 *     @OA\Property(property="sceneOperations", type="integer"),
 *     @OA\Property(property="actionTriggers", type="integer"),
 * ),
 *   @OA\Property(property="supportedFunctions", nullable=true, type="array", @OA\Items(ref="#/components/schemas/ChannelFunction")),
 * )
 */
class ChannelController extends RestController {
    use SuplaServerAware;
    use Transactional;

    /** @var ChannelStateGetter */
    private $channelStateGetter;
    /** @var ChannelActionExecutor */
    private $channelActionExecutor;
    /** @var ScheduleManager */
    private $scheduleManager;
    /** @var IODeviceChannelRepository */
    private $channelRepository;

    public function __construct(
        ChannelStateGetter $channelStateGetter,
        ChannelActionExecutor $channelActionExecutor,
        ScheduleManager $scheduleManager,
        IODeviceChannelRepository $channelRepository
    ) {
        $this->channelStateGetter = $channelStateGetter;
        $this->channelActionExecutor = $channelActionExecutor;
        $this->scheduleManager = $scheduleManager;
        $this->channelRepository = $channelRepository;
    }

    protected function getDefaultAllowedSerializationGroups(Request $request): array {
        $groups = [
            'iodevice', 'location', 'connected', 'state', 'supportedFunctions', 'relationsCount', 'actionTriggers', 'userIcon',
            'location' => 'channel.location',
            'iodevice' => 'channel.iodevice',
            'relationsCount' => 'channel.relationsCount',
            'actionTriggers' => 'channel.actionTriggers',
            'userIcon' => 'channel.userIcon',
        ];
        if (!strpos($request->get('_route'), 'channels_list')) {
            $groups[] = 'iodevice.location';
        }
        return $groups;
    }

    /**
     * @OA\Get(
     *     path="/channels", operationId="getChannels", summary="Get Channels", tags={"Channels"},
     *     @OA\Parameter(name="function", in="query", explode=false, required=false, @OA\Schema(type="array", @OA\Items(ref="#/components/schemas/ChannelFunctionEnumNames"))),
     *     @OA\Parameter(name="io", in="query", description="Return only `input` or `output` channels.", required=false, @OA\Schema(type="string", enum={"input", "output"})),
     *     @OA\Parameter(name="hasFunction", in="query", description="Return only channels with (`true`) or without (`false`) chosen functions.", required=false, @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="skipIds", in="query", explode=false, required=false, @OA\Schema(type="array", @OA\Items(type="integer"))),
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"iodevice", "location", "connected", "state", "supportedFunctions", "relationsCount", "actionTriggers", "userIcon"})),
     *     ),
     *     @OA\Parameter(
     *         description="Select an integration that the channels should be returned for.",
     *         in="query", name="forIntegration", required=false,
     *         @OA\Schema(type="string", enum={"google-home", "alexa"}),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Channel"))),
     * )
     * @Rest\Get(name="channels_list")
     * @Security("has_role('ROLE_CHANNELS_R')")
     */
    public function getChannelsAction(Request $request) {
        $filters = function (QueryBuilder $builder, string $alias) use ($request) {
            if (($function = $request->get('function')) !== null) {
                $functionIds = EntityUtils::mapToIds(ChannelFunction::fromStrings(explode(',', $function)));
                $builder->andWhere("$alias.function IN(:functionIds)")->setParameter('functionIds', $functionIds);
            }
            if (($io = $request->get('io')) !== null) {
                Assertion::inArray($io, ['input', 'output']);
                $functionIds = $io == 'output' ? ChannelFunction::outputFunctions() : ChannelFunction::inputFunctions();
                $builder->andWhere("$alias.function IN(:ioFunctionIds)")->setParameter('ioFunctionIds', $functionIds);
            }
            if (($hasFunction = $request->get('hasFunction')) !== null) {
                if (filter_var($hasFunction, FILTER_VALIDATE_BOOLEAN)) {
                    $builder->andWhere("$alias.function != :noneFunctionId");
                } else {
                    $builder->andWhere("$alias.function = :noneFunctionId");
                }
                $builder->setParameter('noneFunctionId', ChannelFunction::NONE);
            }
            if (($skipIds = $request->get('skipIds')) !== null) {
                $skipIds = array_filter(array_map('intval', explode(',', $skipIds)));
                if ($skipIds) {
                    $builder->andWhere("$alias.id NOT IN(:skipIds)")->setParameter('skipIds', $skipIds);
                }
            }
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
        $channels = $this->channelRepository->findAllForUser($this->getUser(), $filters);
        $channels = $channels->filter(function (IODeviceChannel $channel) {
            return $this->isGranted(AccessIdSecurityVoter::PERMISSION_NAME, $channel);
        });
        $extraGroups = [];
        if (!ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($request)) {
            $extraGroups = ['iodevice.location'];
        }
        $result = $channels->getValues();
        $view = $this->serializedView($result, $request, $extraGroups);
        if (ApiVersions::V2_3()->isRequestedEqualOrGreaterThan($request)) {
            $view->setHeader('X-Total-Count', count($result));
        }
        return $view;
    }

    /**
     * @OA\Get(
     *     path="/channels/{id}", operationId="getChannel", summary="Get Channel", tags={"Channels"},
     *     @OA\Parameter(description="ID", in="path", name="id", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"iodevice", "location", "connected", "state", "supportedFunctions", "relationsCount", "actionTriggers", "userIcon"})),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/Channel")),
     * )
     * @Security("channel.belongsToUser(user) and has_role('ROLE_CHANNELS_R') and is_granted('accessIdContains', channel)")
     */
    public function getChannelAction(Request $request, IODeviceChannel $channel) {
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            if (ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($request)) {
                $extraGroups = ['location.relationsCount', 'channel.relationsCount'];
            } else {
                $extraGroups = ['iodevice.location'];
            }
            return $this->serializedView($channel, $request, $extraGroups);
        } else {
            $enabled = false;
            $connected = false;
            if ($channel->getIoDevice()->getEnabled()) {
                $enabled = true;
                $connected = $this->suplaServer->isChannelConnected($channel);
            }
            $result = array_merge(['connected' => $connected, 'enabled' => $enabled], $this->channelStateGetter->getState($channel));
            return $this->handleView($this->view($result, Response::HTTP_OK));
        }
    }

    /**
     * @OA\Put(
     *     path="/channels/{id}", operationId="updateChannel", tags={"Channels"},
     *     @OA\Parameter(description="ID", in="path", name="id", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(description="Whether to perform actions that require data loss (e.g. delete schedules when changing channel function)", in="query", name="safe", required=false, @OA\Schema(type="boolean")),
     *     @OA\RequestBody(
     *       required=true,
     *       @OA\JsonContent(
     *          @OA\Property(property="functionId", ref="#/components/schemas/ChannelFunctionEnumNames"),
     *          @OA\Property(property="caption", type="string"),
     *          @OA\Property(property="altIcon", type="integer"),
     *          @OA\Property(property="hidden", type="boolean"),
     *          @OA\Property(property="locationId", type="integer"),
     *          @OA\Property(property="inheritedLocation", type="boolean"),
     *          @OA\Property(property="userIconId", type="integer"),
     *          @OA\Property(property="config", ref="#/components/schemas/ChannelConfig"),
     *       ),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/Channel")),
     *     @OA\Response(response="409", description="Channel update would result in data loss, and the safe parameter has been set to true.",
     *       @OA\JsonContent(
     *         @OA\Property(property="channelGroups", type="array", @OA\Items(type="object")),
     *         @OA\Property(property="directLinks", type="array", @OA\Items(type="object")),
     *         @OA\Property(property="schedules", type="array", @OA\Items(type="object")),
     *         @OA\Property(property="sceneOperations", type="array", @OA\Items(type="object")),
     *         @OA\Property(property="actionTriggers", type="array", @OA\Items(type="object")),
     *       )
     *    ),
     * )
     * @Security("channel.belongsToUser(user) and has_role('ROLE_CHANNELS_RW') and is_granted('accessIdContains', channel)")
     * @UnavailableInMaintenance
     */
    public function putChannelAction(
        Request $request,
        IODeviceChannel $channel,
        ChannelDependencies $channelDependencies,
        SubjectConfigTranslator $paramConfigTranslator,
        LocationRepository $locationRepository,
        UserIconRepository $userIconRepository
    ) {
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            $requestData = $request->request->all();
            $newFunction = false;
            if (isset($requestData['functionId'])) {
                $function = ChannelFunction::fromString($requestData['functionId']);
                $functionHasBeenChanged = $function->getId() !== $channel->getFunction()->getId();
                if ($functionHasBeenChanged) {
                    $newFunction = $function;
                    Assertion::inArray(
                        $newFunction->getId(),
                        array_merge([ChannelFunction::NONE], EntityUtils::mapToIds(ChannelFunction::forChannel($channel))),
                        'Invalid function for channel.' // i18n
                    );
                    $shouldConfirm = ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($request)
                        ? filter_var($request->get('safe', false), FILTER_VALIDATE_BOOLEAN)
                        : !$request->get('confirm');
                    if ($shouldConfirm) {
                        $dependencies = $channelDependencies->getDependencies($channel);
                        if (array_filter($dependencies)) {
                            $view = $this->view($dependencies, Response::HTTP_CONFLICT);
                            $this->setSerializationGroups($view, $request, ['scene'], ['scene']);
                            return $view;
                        }
                    }
                    $cannotChangeMsg = 'Cannot change the channel function right now.'; // i18n
                    Assertion::true($this->suplaServer->userAction('BEFORE-CHANNEL-FUNCTION-CHANGE', $channel->getId()), $cannotChangeMsg);
                }
            }
            $channelConfig = $requestData['config'] ?? $paramConfigTranslator->getConfig($channel);
            if (!ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($request)) {
                $channelToReadConfig = new IODeviceChannel();
                EntityUtils::setField($channelToReadConfig, 'id', $channel->getId());
                EntityUtils::setField($channelToReadConfig, 'type', $channel->getType()->getId());
                $functionId = $newFunction ? $newFunction->getId() : $channel->getFunction()->getId();
                EntityUtils::setField($channelToReadConfig, 'function', $functionId);
                $channelToReadConfig->setParam1($requestData['param1'] ?? 0);
                $channelToReadConfig->setParam2($requestData['param2'] ?? 0);
                $channelToReadConfig->setParam3($requestData['param3'] ?? 0);
                $channelToReadConfig->setParam4($requestData['param4'] ?? 0);
                $channelToReadConfig->setTextParam1($requestData['textParam1'] ?? null);
                $channelToReadConfig->setTextParam2($requestData['textParam2'] ?? null);
                $channelToReadConfig->setTextParam3($requestData['textParam3'] ?? null);
                $channelConfig = $paramConfigTranslator->getConfig($channelToReadConfig);
            }

            if (isset($requestData['inheritedLocation']) && $requestData['inheritedLocation']) {
                $channel->setLocation(null);
            } elseif (isset($requestData['locationId'])) {
                Assertion::integer($requestData['locationId']);
                $location = $locationRepository->findForUser($channel->getUser(), $requestData['locationId']);
                $channel->setLocation($location);
            }
            if (isset($requestData['caption'])) {
                Assertion::string($requestData['caption']);
                Assertion::maxLength($requestData['caption'], 100, 'Caption is too long.'); // i18n
                $channel->setCaption($requestData['caption']);
            }
            if (isset($requestData['hidden'])) {
                Assertion::boolean($requestData['hidden']);
                $channel->setHidden($requestData['hidden']);
            }
            /** @var IODeviceChannel $channel */
            $channel = $this->transactional(function (EntityManagerInterface $em) use (
                $userIconRepository,
                $requestData,
                $newFunction,
                $channelConfig,
                $paramConfigTranslator,
                $channelDependencies,
                $request,
                $channel
            ) {
                if ($newFunction) {
                    $paramConfigTranslator->clearConfig($channel);
                    $channel->setFunction($newFunction);
                    $channelDependencies->clearDependencies($channel);
                    $channel->setUserIcon(null);
                    $channel->setAltIcon(0);
                    $em->persist($channel);
                }
                $paramConfigTranslator->setConfig($channel, $channelConfig);
                $channel->setUserConfig($paramConfigTranslator->getConfig($channel));
                if (isset($requestData['altIcon'])) {
                    Assertion::integer($requestData['altIcon']);
                    $channel->setAltIcon($requestData['altIcon']);
                }
                if (array_key_exists('userIconId', $requestData)) {
                    if ($requestData['userIconId'] === null) {
                        $channel->setUserIcon(null);
                    } else {
                        Assertion::integer($requestData['userIconId']);
                        $icon = $userIconRepository->findForUser($channel->getUser(), $requestData['userIconId']);
                        Assertion::eq(
                            $icon->getFunction()->getId(),
                            $channel->getFunction()->getId(),
                            'Chosen user icon is for other function.'
                        );
                        $channel->setUserIcon($icon);
                    }
                }
                $em->persist($channel);
                $atIndex = 1;
                foreach ($this->channelRepository->findActionTriggers($channel) as $actionTrigger) {
                    $caption = trim(sprintf('%s AT#%d', $channel->getCaption(), $atIndex++));
                    $actionTrigger->setCaption($caption);
                    $em->persist($actionTrigger);
                }
                return $channel;
            });
            $this->suplaServer->onDeviceSettingsChanged($channel->getIoDevice());
            $this->suplaServer->reconnect();
            return $this->getChannelAction($request, $channel->clearRelationsCount());
        } else {
            $actionParams = json_decode($request->getContent(), true);
            $this->channelActionExecutor->executeAction($channel, ChannelFunctionAction::SET_RGBW_PARAMETERS(), $actionParams);
            return $this->handleView($this->view(null, Response::HTTP_OK));
        }
    }

    /**
     * @OA\Patch(
     *     path="/channels/{id}", operationId="executeAction", tags={"Channels"},
     *     @OA\Parameter(description="ID", in="path", name="id", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *       required=true,
     *       @OA\JsonContent(
     *          description="Defines an action to execute on channel. The `action` key is always required. The rest of the keys are params depending on the chosen action.",
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
     *          @OA\Property(property="message", type="string", example="Cannot execute requested action on this channel."),
     *     )),
     * )
     * @Security("channel.belongsToUser(user) and has_role('ROLE_CHANNELS_EA') and is_granted('accessIdContains', channel)")
     */
    public function patchChannelAction(Request $request, IODeviceChannel $channel) {
        $params = json_decode($request->getContent(), true);
        Assertion::keyExists($params, 'action', 'Missing action.');
        $action = ChannelFunctionAction::fromString($params['action']);
        unset($params['action']);
        $this->channelActionExecutor->executeAction($channel, $action, $params);
        $status = ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;
        $status = ApiVersions::V2_3()->isRequestedEqualOrGreaterThan($request) ? Response::HTTP_ACCEPTED : $status;
        return $this->handleView($this->view(null, $status));
    }

    /**
     * @OA\Patch(
     *     path="/channels/{id}/settings", operationId="configureChannel", tags={"Channels"},
     *     @OA\Parameter(description="ID", in="path", name="id", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *       required=true,
     *       @OA\JsonContent(
     *          @OA\Property(property="action", type="string", enum={"resetCounters", "recalibrate"})
     *       ),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/Channel")),
     * )
     * @Rest\Patch("/channels/{channel}/settings")
     * @Security("channel.belongsToUser(user) and has_role('ROLE_CHANNELS_RW') and is_granted('accessIdContains', channel)")
     */
    public function patchChannelSettingsAction(
        Request $request,
        IODeviceChannel $channel,
        SubjectConfigTranslator $paramConfigTranslator
    ) {
        $body = json_decode($request->getContent(), true);
        Assertion::keyExists($body, 'action', 'Missing action.');
        $channelConfig = $paramConfigTranslator->getConfig($channel);
        $channel = $this->transactional(function (EntityManagerInterface $em) use ($body, $channel, $channelConfig) {
            $action = $body['action'];
            if ($action === 'resetCounters') {
                Assertion::true($channelConfig['resetCountersAvailable'] ?? false, 'Cannot reset counters of this channel.');
                $result = $this->suplaServer->channelAction($channel, 'RESET-COUNTERS');
                Assertion::true($result, 'Could not reset the counters.');
            } elseif ($action === 'recalibrate') {
                Assertion::true($channelConfig['recalibrateAvailable'] ?? false, 'Cannot recalibrate this channel.');
                $result = $this->suplaServer->channelAction($channel, 'RECALIBRATE');
                Assertion::true($result, 'Could not recalibrate.');
            } else {
                throw new ApiException('Invalid action given.');
            }
            $em->persist($channel);
            return $channel;
        });
        return $this->getChannelAction($request, $channel->clearRelationsCount());
    }

    /**
     * @Security("ioDevice.belongsToUser(user) and has_role('ROLE_CHANNELS_R') and is_granted('accessIdContains', ioDevice)")
     */
    public function getIodeviceChannelsAction(Request $request, IODevice $ioDevice) {
        $channels = $this->channelRepository->findAllForUser(
            $this->getUser(),
            function (QueryBuilder $query, string $alias) use ($ioDevice) {
                $query->andWhere("$alias.iodevice = :io")->setParameter('io', $ioDevice);
            }
        );
        return $this->serializedView($channels, $request);
    }
}
