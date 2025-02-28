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
use FOS\RestBundle\Controller\Annotations as Rest;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Auth\Voter\AccessIdSecurityVoter;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\SubDevice;
use SuplaBundle\EventListener\UnavailableInMaintenance;
use SuplaBundle\Exception\ApiException;
use SuplaBundle\Message\Emails\ConfirmationAfterDeviceUnlockEmailNotification;
use SuplaBundle\Message\Emails\ConfirmDeviceUnlockEmailNotification;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\Dependencies\ChannelDependencies;
use SuplaBundle\Model\Dependencies\IODeviceDependencies;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Model\UserConfigTranslator\HiddenReadOnlyConfigFieldsUserConfigTranslator;
use SuplaBundle\Model\UserConfigTranslator\IODeviceConfigTranslator;
use SuplaBundle\Repository\IODeviceChannelRepository;
use SuplaBundle\Repository\IODeviceRepository;
use SuplaBundle\Serialization\RequestFiller\IODeviceRequestFiller;
use SuplaBundle\Supla\SuplaAutodiscover;
use SuplaBundle\Supla\SuplaServerAware;
use SuplaBundle\Utils\ArrayUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Schema(
 *   schema="Device", type="object",
 *   @OA\Property(property="id", type="integer", description="Identifier"),
 *   @OA\Property(property="name", type="string", description="Device name set in the firmware."),
 *   @OA\Property(property="comment", type="string", description="Device caption / comment given by the user."),
 *   @OA\Property(property="gUIDString", type="string", description="Unique device identifier (GUID)."),
 *   @OA\Property(property="enabled", type="boolean"),
 *   @OA\Property(property="lastConnected", type="string", format="date-time"),
 *   @OA\Property(property="lastIpv4", type="string", format="ipv4"),
 *   @OA\Property(property="regDate", type="string", format="date-time"),
 *   @OA\Property(property="regIpv4", type="string", format="ipv4"),
 *   @OA\Property(property="softwareVersion", type="string"),
 *   @OA\Property(property="productId", type="integer"),
 *   @OA\Property(property="manufacturer", type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="caption", type="string"),
 *     @OA\Property(property="name", type="string"),
 *   ),
 *   @OA\Property(property="locationId", type="integer"),
 *   @OA\Property(property="location", description="Device location, if requested by the `include` param", ref="#/components/schemas/Location"),
 *   @OA\Property(property="originalLocationId", type="integer"),
 *   @OA\Property(property="originalLocation", description="Device location that was specified in the device config during the first connection, if requested by the `include` param", ref="#/components/schemas/Location"),
 *   @OA\Property(property="channels", type="array", description="Channels that belongs to this device, if requested by the `include` param", @OA\Items(ref="#/components/schemas/Channel")),
 *   @OA\Property(property="connected", type="boolean", description="Whether the device is now connected to the SUPLA Server."),
 *   @OA\Property(property="relationsCount", description="Counts of related entities.",
 *     @OA\Property(property="channels", type="integer"),
 *     @OA\Property(property="channelsWithConflict", type="integer"),
 *   ),
 *   @OA\Property(property="enterConfigurationModeAvailable", type="boolean"),
 *   @OA\Property(property="flags", type="object"),
 *   @OA\Property(property="isSleepModeEnabled", type="boolean"),
 *   @OA\Property(property="config", ref="#/components/schemas/DeviceConfig"),
 *   @OA\Property(property="pairingResult", type="object"),
 * )
 */
class IODeviceController extends RestController {
    use SuplaServerAware;
    use Transactional;

    /** @var IODeviceChannelRepository */
    private $iodeviceRepository;

    public function __construct(IODeviceRepository $iodeviceRepository) {
        $this->iodeviceRepository = $iodeviceRepository;
    }

    protected function getDefaultAllowedSerializationGroups(Request $request): array {
        $groups = [
            'channels', 'location', 'originalLocation', 'connected', 'accessids', 'state', 'pairingResult',
            'channels' => 'iodevice.channels',
            'location' => 'iodevice.location',
            'originalLocation' => 'iodevice.originalLocation',
            'accessids' => 'location.accessids',
        ];
        if (!ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($request)) {
            $groups[] = 'schedules';
            $groups['schedules'] = 'iodevice.schedules';
        }
        return $groups;
    }

    /**
     * @OA\Get(
     *     path="/iodevices", operationId="getIoDevices", summary="Get Devices", tags={"Devices"},
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"channels", "location", "originalLocation", "connected", "accessids"})),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Device"))),
     * )
     * @Rest\Get("/iodevices")
     * @Security("is_granted('ROLE_IODEVICES_R')")
     */
    public function getIodevicesAction(Request $request) {
        $result = [];
        $user = $this->getUser();
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            $result = $this->iodeviceRepository->findAllForUser($this->getUser());
            $result = $result->filter(
                function (IODevice $device) {
                    return $this->isGranted(AccessIdSecurityVoter::PERMISSION_NAME, $device);
                }
            );
            $result = $result->getValues();
        } else {
            if ($user !== null) {
                foreach ($user->getIODevices() as $device) {
                    $channels = [];
                    foreach ($device->getChannels() as $channel) {
                        $channels[] = [
                            'id' => $channel->getId(),
                            'chnnel_number' => $channel->getChannelNumber(),
                            'caption' => $channel->getCaption(),
                            'type' => [
                                'name' => 'TYPE_' . $channel->getType()->getName(),
                                'id' => $channel->getType()->getId(),
                            ],
                            'function' => [
                                'name' => 'FNC_' . $channel->getFunction()->getName(),
                                'id' => $channel->getFunction()->getId(),
                            ],
                        ];
                    }
                    $result[] = [
                        'id' => $device->getId(),
                        'location_id' => $device->getLocation()->getId(),
                        'enabled' => $device->getEnabled(),
                        'name' => $device->getName(),
                        'comment' => $device->getComment(),
                        'registration' => [
                            'date' => $device->getRegDate()->getTimestamp(),
                            'ip_v4' => $device->getRegIpv4(),
                        ],
                        'last_connected' => [
                            'date' => $device->getLastConnected()->getTimestamp(),
                            'ip_v4' => $device->getLastIpv4(),
                        ],
                        'guid' => $device->getGUIDString(),
                        'software_version' => $device->getSoftwareVersion(),
                        'protocol_version' => $device->getProtocolVersion(),
                        'channels' => $channels,
                    ];
                }
            }
            $result = ['iodevices' => $result];
        }
        $view = $this->serializedView($result, $request);
        if (ApiVersions::V2_3()->isRequestedEqualOrGreaterThan($request)) {
            $view->setHeader('X-Total-Count', count($result));
        }
        return $view;
    }

    /**
     * @OA\Get(
     *     path="/iodevices/{id}", operationId="getIoDevice", summary="Get Device", tags={"Devices"},
     *     @OA\Parameter(description="ID", in="path", name="id", required=true, @OA\Schema(oneOf={
     *       @OA\Schema(type="integer"), @OA\Schema(type="string"),
     *     })),
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"channels", "location", "originalLocation", "connected", "accessids", "pairingResult"})),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/Device")),
     * )
     * @Rest\Get("/iodevices/{ioDevice}", requirements={"ioDevice"="^\d+$"})
     * @Security("ioDevice.belongsToUser(user) and is_granted('ROLE_IODEVICES_R') and is_granted('accessIdContains', ioDevice)")
     */
    public function getIodeviceAction(Request $request, IODevice $ioDevice) {
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            $result = $ioDevice;
        } else {
            $enabled = $ioDevice->getEnabled();
            $connected = $this->suplaServer->isDeviceConnected($ioDevice);

            $channels = [];

            foreach ($ioDevice->getChannels() as $channel) {
                $channels[] = [
                    'id' => $channel->getId(),
                    'chnnel_number' => $channel->getChannelNumber(),
                    'caption' => $channel->getCaption(),
                    'type' => [
                        'name' => 'TYPE_' . $channel->getType()->getName(),
                        'id' => $channel->getType()->getId(),
                    ],
                    'function' => [
                        'name' => 'FNC_' . $channel->getFunction()->getName(),
                        'id' => $channel->getFunction()->getId(),
                    ],
                ];
            }

            $result[] = [
                'id' => $ioDevice->getId(),
                'location_id' => $ioDevice->getLocation()->getId(),
                'enabled' => $enabled,
                'connected' => $connected,
                'name' => $ioDevice->getName(),
                'comment' => $ioDevice->getComment(),
                'registration' => [
                    'date' => $ioDevice->getRegDate()->getTimestamp(),
                    'ip_v4' => $ioDevice->getRegIpv4(),
                ],
                'last_connected' => [
                    'date' => $ioDevice->getLastConnected()->getTimestamp(),
                    'ip_v4' => $ioDevice->getLastIpv4(),
                ],
                'guid' => $ioDevice->getGUIDString(),
                'software_version' => $ioDevice->getSoftwareVersion(),
                'protocol_version' => $ioDevice->getProtocolVersion(),
                'channels' => $channels,
            ];
        }
        return $this->serializedView($result, $request, ['location.relationsCount', 'iodevice.relationsCount']);
    }

    /**
     * Documented above (id oneOf).
     * @Security("is_granted('ROLE_IODEVICES_R')")
     * @Rest\Get("/iodevices/{guid}")
     */
    public function getIodeviceByGuidAction(Request $request, string $guid, IODeviceRepository $repository) {
        $ioDevice = $repository->findForUserByGuid($this->getUser(), $guid);
        $this->denyAccessUnlessGranted('accessIdContains', $ioDevice);
        return $this->getIodeviceAction($request, $ioDevice);
    }

    /**
     * @OA\Put(
     *     path="/iodevices/{id}", operationId="updateDevice", tags={"Devices"},
     *     @OA\Parameter(description="ID", in="path", name="id", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(description="Whether to perform actions that require data loss (e.g. disable schedules when disabling the device)", in="query", name="safe", required=false, @OA\Schema(type="boolean")),
     *     @OA\RequestBody(
     *       required=true,
     *       @OA\JsonContent(
     *          @OA\Property(property="enabled", type="boolean"),
     *          @OA\Property(property="comment", type="string"),
     *          @OA\Property(property="locationId", type="integer"),
     *          @OA\Property(property="config", ref="#/components/schemas/DeviceConfig"),
     *          @OA\Property(property="configBefore", ref="#/components/schemas/DeviceConfig"),
     *       )
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/Device")),
     *     @OA\Response(response="409", description="Device update would result in data loss, and the safe parameter has been set to true."),
     * )
     * @Rest\Put("/iodevices/{ioDevice}")
     * @Security("ioDevice.belongsToUser(user) and is_granted('ROLE_IODEVICES_RW') and is_granted('accessIdContains', ioDevice)")
     * @UnavailableInMaintenance
     */
    public function putIodeviceAction(
        Request $request,
        IODevice $ioDevice,
        IODeviceRequestFiller $requestFiller,
        IODeviceDependencies $deviceDependencies,
        IODeviceConfigTranslator $configTranslator
    ) {
        $result = $this->transactional(function (EntityManagerInterface $em) use (
            $requestFiller,
            $request,
            $ioDevice,
            $deviceDependencies,
            $configTranslator
        ) {
            $requestData = $request->request->all();
            $enabledChanged = array_key_exists('enabled', $requestData) && $ioDevice->getEnabled() !== $requestData['enabled'];
            $locationChanged = array_key_exists('locationId', $requestData)
                && $ioDevice->getLocation()->getId() !== $requestData['locationId'];
            $shouldAsk = ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($request)
                ? filter_var($request->get('safe', false), FILTER_VALIDATE_BOOLEAN)
                : !$request->get('confirm', false);
            if ($shouldAsk) {
                if ($locationChanged) {
                    $locationDependencies = $deviceDependencies->getItemsThatDependOnLocation($ioDevice);
                    $visibleDependencies = $deviceDependencies->onlyDependenciesVisibleToUser($locationDependencies);
                    if (array_filter($visibleDependencies) && $shouldAsk) {
                        return $this->view(['conflictOn' => 'location', 'dependencies' => $visibleDependencies], Response::HTTP_CONFLICT);
                    }
                }
                if ($enabledChanged && !$requestData['enabled']) {
                    $dependencies = $deviceDependencies->getItemsThatDependOnEnabled($ioDevice);
                    $dependencies = $deviceDependencies->onlyDependenciesVisibleToUser($dependencies);
                    $dependencies = $deviceDependencies->onlyDependenciesFromOtherDevices($dependencies, $ioDevice);
                    if (array_filter($dependencies)) {
                        $view = $this->view(['conflictOn' => 'enabled', 'dependencies' => $dependencies], Response::HTTP_CONFLICT);
                        $this->setSerializationGroups(
                            $view,
                            $request,
                            ['scene', 'reaction.owningChannel'],
                            ['scene', 'reaction.owningChannel']
                        );
                        return $view;
                    }
                }
            }
            if (isset($requestData['config']) && ApiVersions::V3()->isRequestedEqualOrGreaterThan($request)) {
                Assertion::isArray($requestData['config'], null, 'config');
                $currentConfig = json_decode(json_encode($configTranslator->getConfig($ioDevice)), true);
                if ($shouldAsk || isset($requestData['configBefore'])) {
                    Assertion::keyExists($requestData, 'configBefore', 'You need to provide a configuration that has been fetched.');
                    Assertion::isArray($requestData['configBefore'], null, 'configBefore');
                    $configBefore = $requestData['configBefore'];
                } else {
                    $configBefore = $currentConfig;
                }
                $requestData['config'] = ArrayUtils::mergeConfigs($configBefore, $requestData['config'], $currentConfig);
            }
            $requestFiller->fillFromData($requestData, $ioDevice);
            $em->persist($ioDevice);
            if ($locationChanged) {
                $deviceDependencies->updateLocation($ioDevice, $ioDevice->getLocation());
            }
            if ($enabledChanged && !$ioDevice->getEnabled()) {
                $deviceDependencies->disableDependencies($ioDevice);
            }
            return $this->serializedView($ioDevice, $request, ['iodevice.schedules']);
        });
        return $result;
    }

    /**
     * @OA\Patch(
     *     path="/iodevices/{id}", operationId="executeDeviceAction", tags={"Devices"},
     *     @OA\Parameter(description="ID", in="path", name="id", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *       required=true,
     *       @OA\JsonContent(
     *          @OA\Property(property="action", type="string"),
     *       )
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/Device")),
     *    ),
     * )
     * @Security("ioDevice.belongsToUser(user) and is_granted('ROLE_IODEVICES_RW') and is_granted('accessIdContains', ioDevice)")
     * @Rest\Patch("/iodevices/{ioDevice}", requirements={"ioDevice"="^\d+$"})
     * @UnavailableInMaintenance
     */
    public function patchIodeviceAction(Request $request, IODevice $ioDevice, SuplaAutodiscover $ad) {
        $body = json_decode($request->getContent(), true);
        Assertion::keyExists($body, 'action', 'Missing action.');
        $device = $this->transactional(function (EntityManagerInterface $em) use ($ad, $body, $ioDevice) {
            $action = $body['action'];
            if ($action === 'enterConfigurationMode') {
                Assertion::true(
                    $ioDevice->isEnterConfigurationModeAvailable(),
                    'Entering configuration mode is unsupported in the firmware.' // i18n
                );
                $result = $this->suplaServer->deviceAction($ioDevice, 'ENTER-CONFIGURATION-MODE');
                Assertion::true($result, 'Could not enter the configuration mode.'); // i18n
            } elseif ($action === 'restartDevice') {
                Assertion::true(
                    $ioDevice->isRemoteRestartAvailable(),
                    'Remote restart is unsupported in the firmware.' // i18n
                );
                $result = $this->suplaServer->deviceAction($ioDevice, 'RESTART-DEVICE');
                Assertion::true($result, 'Could not restart the device.'); // i18n
            } elseif ($action === 'pairSubdevice') {
                Assertion::true(
                    $ioDevice->getFlags()['pairingSubdevicesAvailable'],
                    'Pairing subdevices is unsupported in the firmware.' // i18n
                );
                $result = $this->suplaServer->deviceAction($ioDevice, 'PAIR-SUBDEVICE');
                Assertion::true($result, 'Could not enter the configuration mode.'); // i18n
            } elseif ($action === 'identifyDevice') {
                Assertion::true(
                    $ioDevice->getFlags()['identifyDeviceAvailable'],
                    'Device identification is unsupported in the firmware.' // i18n
                );
                $result = $this->suplaServer->deviceAction($ioDevice, 'IDENTIFY-DEVICE');
                Assertion::true($result, 'Could not send the identify command.'); // i18n
            } elseif ($action === 'setTime') {
                Assertion::keyExists($body, 'time', 'Missing time.');
                $timestamp = strtotime($body['time']);
                Assertion::integer($timestamp, 'Unsupported date format given.');
                $timezoneName = $this->getCurrentUserOrThrow()->getTimezone();
                $dateTime = new \DateTime($body['time']);
                $dateTime->setTimezone(new \DateTimeZone($timezoneName));
                $suplaDayOfWeek = intval($dateTime->format('w')) + 1; // 1 - Sunday, ... 7 - Monday
                $params = $dateTime->format("Y,n,j,$suplaDayOfWeek,G,i,s,") . base64_encode($timezoneName);
                $result = $this->suplaServer->deviceAction($ioDevice, 'DEVICE-SET-TIME', [$params]);
                Assertion::true($result, 'Could not set the device time.'); // i18n
            } elseif ($action === 'unlock') {
                Assertion::keyExists($body, 'email');
                Assertion::email($body['email']);
                $unlockCode = $ad->requestDeviceUnlockCode($ioDevice, $body['email']);
                $email = new ConfirmDeviceUnlockEmailNotification($body['email'], $ioDevice, $unlockCode);
                $this->dispatchMessage($email);
            } else {
                throw new ApiException('Invalid action given.');
            }
            $em->persist($ioDevice);
            return $ioDevice;
        });
        return $this->getIodeviceAction($request, $device->clearRelationsCount());
    }

    /**
     * @OA\Get(
     *     path="/subdevices", operationId="getSubDevices", tags={"Devices"},
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(type="array", @OA\Items(type="object")),
     *    ),
     * )
     * @Rest\Get("/subdevices")
     * @Security("is_granted('ROLE_IODEVICES_R')")
     */
    public function getSubdevicesAction(Request $request) {
        $subdevices = $this->entityManager->getRepository(SubDevice::class)
            ->createQueryBuilder('sd')
            ->innerJoin('sd.device', 'io')
            ->where('io.user = :user')
            ->setParameter('user', $this->getCurrentUser())
            ->getQuery()
            ->getResult();
        return $this->serializedView($subdevices, $request);
    }

    /**
     * @OA\Delete(
     *     path="/iodevices/{id}", operationId="deleteDevice", summary="Delete the device", tags={"Devices"},
     *     @OA\Parameter(description="ID", in="path", name="id", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(description="Whether to perform actions that require data loss (e.g. delete schedules when deleting the device)", in="query", name="safe", required=false, @OA\Schema(type="boolean")),
     *     @OA\Response(response="204", description="Success"),
     *     @OA\Response(response="409", description="Device deletion would result in data loss, and the safe parameter has been set to true."),
     * )
     * @Rest\Delete("/iodevices/{ioDevice}")
     * @Security("ioDevice.belongsToUser(user) and is_granted('ROLE_IODEVICES_RW') and is_granted('accessIdContains', ioDevice)")
     * @UnavailableInMaintenance
     */
    public function deleteIodeviceAction(
        IODevice $ioDevice,
        Request $request,
        IODeviceDependencies $deviceDependencies,
        ChannelDependencies $channelDependencies,
        HiddenReadOnlyConfigFieldsUserConfigTranslator $hiddenConfigTranslator
    ) {
        $deviceId = $ioDevice->getId();
        if (filter_var($request->get('safe', false), FILTER_VALIDATE_BOOLEAN)) {
            $dependencies = $deviceDependencies->getItemsThatDependOnEnabled($ioDevice);
            $dependencies = $deviceDependencies->onlyDependenciesVisibleToUser($dependencies);
            $dependencies = $deviceDependencies->onlyDependenciesFromOtherDevices($dependencies, $ioDevice);
            if (count(array_filter($dependencies))) {
                $view = $this->view(['conflictOn' => 'deletion', 'dependencies' => $dependencies], Response::HTTP_CONFLICT);
                $this->setSerializationGroups(
                    $view,
                    $request,
                    ['scene', 'reaction.owningChannel'],
                    ['scene', 'reaction.owningChannel']
                );
                return $view;
            }
        }
        $cannotDeleteMsg = 'Cannot delete this I/O Device right now.'; // i18n
        Assertion::true($this->suplaServer->userAction('BEFORE-DEVICE-DELETE', $ioDevice->getId()), $cannotDeleteMsg);
        $this->transactional(function (EntityManagerInterface $em) use ($hiddenConfigTranslator, $channelDependencies, $ioDevice) {
            $hiddenConfigTranslator->setIdsToIgnore(EntityUtils::mapToIds($ioDevice->getChannels()));
            foreach ($ioDevice->getChannels() as $channel) {
                $channelDependencies->clearDependencies($channel);
            }
            foreach ($ioDevice->getChannels() as $channel) {
                $em->remove($channel);
            }
            $em->remove($ioDevice);
        });
        $this->suplaServer->reconnect();
        $this->suplaServer->userAction('ON-DEVICE-DELETED', $deviceId);
        return new Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Patch("/confirm-device-unlock/{device}")
     * @UnavailableInMaintenance
     */
    public function confirmDeviceUnlockAction(IODevice $device, Request $request, SuplaAutodiscover $ad) {
        if (!$device->isLocked()) {
            throw $this->createNotFoundException();
        }
        $body = json_decode($request->getContent(), true);
        Assertion::isArray($body);
        Assertion::keyExists($body, 'code');
        $unlockCode = $body['code'];
        Assertion::string($unlockCode);
        $ad->unlockDevice($device, $unlockCode);
        $this->transactional(function (EntityManagerInterface $em) use ($device) {
            $device->unlock();
            $em->persist($device);
        });
        $email = new ConfirmationAfterDeviceUnlockEmailNotification($device);
        $this->dispatchMessage($email);
        return $this->view($device);
    }
}
