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

namespace SuplaApiBundle\Controller;

use Assert\Assertion;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaApiBundle\Model\ApiVersions;
use SuplaApiBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaApiBundle\Model\ChannelParamsUpdater\ChannelParamsUpdater;
use SuplaApiBundle\Model\ChannelStateGetter\ChannelStateGetter;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\IODeviceManager;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApiChannelController extends RestController {
    use SuplaServerAware;
    use Transactional;

    const RECORD_LIMIT_PER_REQUEST = 5000;

    /** @var IODeviceManager */
    private $deviceManager;
    /** @var ChannelParamsUpdater */
    private $channelParamsUpdater;
    /** @var ChannelStateGetter */
    private $channelStateGetter;
    /** @var ChannelActionExecutor */
    private $channelActionExecutor;

    public function __construct(
        IODeviceManager $deviceManager,
        ChannelParamsUpdater $channelParamsUpdater,
        ChannelStateGetter $channelStateGetter,
        ChannelActionExecutor $channelActionExecutor
    ) {
        $this->deviceManager = $deviceManager;
        $this->channelParamsUpdater = $channelParamsUpdater;
        $this->channelStateGetter = $channelStateGetter;
        $this->channelActionExecutor = $channelActionExecutor;
    }

    /**
     * @api {get} /channels List
     * @apiDescription Get the list of the channels.
     * @apiGroup Channels
     * @apiVersion 2.2.0
     * @apiParam {string} include Comma-separated list of what to fetch for every Channel.
     * Available options: `iodevice`, `location`.
     * @apiParamExample {GET} GET param to fetch Channels' IO Device and location
     * include=iodevice,location
     * @apiParam {string} function Comma-separated list of functions which channel must have to include in the response. Both names
     * and function identifiers are supported. See [the wiki](https://github.com/SUPLA/supla-cloud/wiki/Channel-Functions-states) for
     * available functions.
     * @apiParamExample {GET} GET param to return only temperature and humidity sensors
     * function=THERMOMETER,HUMIDITY,HUMIDITYANDTEMPERATURE
     * @apiParam {string} io Specify whether the returned channels should be input or output. Only `input` and `output` values are allowed.
     * @apiParam {boolean} hasFunction Allows to return only channels that has some function choosen (i.e. filter out the `NONE` function
     * channels). Can be either `true` or `false`.
     */
    public function getChannelsAction(Request $request) {
        $criteria = Criteria::create();
        if (($function = $request->get('function')) !== null) {
            $functionIds = array_map(function ($fnc) {
                return ChannelFunction::isValidKey($fnc)
                    ? ChannelFunction::$fnc()->getValue()
                    : (new ChannelFunction((int)$fnc))->getValue();
            }, explode(',', $function));
            $criteria->andWhere(Criteria::expr()->in('function', $functionIds));
        }
        if (($io = $request->get('io')) !== null) {
            Assertion::inArray($io, ['input', 'output']);
            $criteria->andWhere(
                Criteria::expr()->in('type', $io == 'output'
                    ? ChannelType::outputTypes()
                    : ChannelType::inputTypes())
            );
        }
        if (($hasFunction = $request->get('hasFunction')) !== null) {
            if ($hasFunction) {
                $criteria->andWhere(Criteria::expr()->neq('function', ChannelFunction::NONE));
            } else {
                $criteria->andWhere(Criteria::expr()->eq('function', ChannelFunction::NONE));
            }
        }
        $channels = $this->getCurrentUser()->getChannels()->matching($criteria);
        $view = $this->view($channels, Response::HTTP_OK);
        $this->setSerializationGroups($view, $request, ['iodevice', 'location']);
        return $view;
    }

    protected function getTempHumidityLogCountAction($th, IODeviceChannel $channel) {
        $f = [];

        if ($th === true) {
            $f[] = ChannelFunction::HUMIDITYANDTEMPERATURE;
        } else {
            $f[] = ChannelFunction::THERMOMETER;
        }

        Assertion::inArray($channel->getFunction()->getId(), $f, 'The requested function is not available on this device');

        $em = $this->container->get('doctrine')->getManager();
        $rep = $em->getRepository('SuplaBundle:' . ($th === true ? 'TempHumidityLogItem' : 'TemperatureLogItem'));

        $query = $rep->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->where('f.channel_id = :id')
            ->setParameter('id', $channel->getId())
            ->getQuery();

        return $this->handleView($this->view(
            ['count' => $query->getSingleScalarResult(),
                'record_limit_per_request' => ApiChannelController::RECORD_LIMIT_PER_REQUEST],
            Response::HTTP_OK
        ));
    }

    protected function temperatureLogItems($channelid, $offset, $limit) {
        $sql = "SELECT UNIX_TIMESTAMP(CONVERT_TZ(`date`, '+00:00', 'SYSTEM')) AS date_timestamp, `temperature` ";
        $sql .= "FROM `supla_temperature_log` WHERE channel_id = ? LIMIT ? OFFSET ?";
        $stmt = $this->container->get('doctrine')->getManager()->getConnection()->prepare($sql);
        $stmt->bindValue(1, $channelid, 'integer');
        $stmt->bindValue(2, $limit, 'integer');
        $stmt->bindValue(3, $offset, 'integer');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    protected function temperatureAndHumidityLogItems($channelid, $offset, $limit) {
        $sql = "SELECT UNIX_TIMESTAMP(CONVERT_TZ(`date`, '+00:00', 'SYSTEM')) AS date_timestamp, `temperature`, ";
        $sql .= "`humidity` FROM `supla_temphumidity_log` WHERE channel_id = ? LIMIT ? OFFSET ?";
        $stmt = $this->container->get('doctrine')->getManager()->getConnection()->prepare($sql);
        $stmt->bindValue(1, $channelid, 'integer');
        $stmt->bindValue(2, $limit, 'integer');
        $stmt->bindValue(3, $offset, 'integer');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    protected function getTempHumidityLogItemsAction($th, IODeviceChannel $channel, $offset, $limit) {
        $f[] = $th === true ? ChannelFunction::HUMIDITYANDTEMPERATURE : ChannelFunction::THERMOMETER;

        Assertion::inArray($channel->getFunction()->getId(), $f, 'The requested function is not available on this device');

        $offset = intval($offset);
        $limit = intval($limit);

        if ($limit <= 0) {
            $limit = ApiChannelController::RECORD_LIMIT_PER_REQUEST;
        }

        if ($th === true) {
            $result = $this->temperatureAndHumidityLogItems($channel->getId(), $offset, $limit);
        } else {
            $result = $this->temperatureLogItems($channel->getId(), $offset, $limit);
        }

        return $this->handleView($this->view(['log' => $result], Response::HTTP_OK));
    }

    /**
     * @Rest\Get("/channels/{channel}/temperature-log-count")
     * @Security("channel.belongsToUser(user)")
     */
    public function getTempLogCountAction(IODeviceChannel $channel) {
        return $this->getTempHumidityLogCountAction(false, $channel);
    }

    /**
     * @Rest\Get("/channels/{channel}/temperature-and-humidity-count")
     * @Security("channel.belongsToUser(user)")
     */
    public function getTempHumLogCountAction(IODeviceChannel $channel) {
        return $this->getTempHumidityLogCountAction(true, $channel);
    }

    /**
     * @Rest\Get("/channels/{channel}/temperature-log-items")
     * @Security("channel.belongsToUser(user)")
     */
    public function getTempLogItemsAction(Request $request, IODeviceChannel $channel) {
        return $this->getTempHumidityLogItemsAction(false, $channel, @$request->query->get('offset'), @$request->query->get('limit'));
    }

    /**
     * @Rest\Get("/channels/{channel}/temperature-and-humidity-items")
     * @Security("channel.belongsToUser(user)")
     */
    public function getTempHumLogItemsAction(Request $request, IODeviceChannel $channel) {
        return $this->getTempHumidityLogItemsAction(true, $channel, @$request->query->get('offset'), @$request->query->get('limit'));
    }

    /**
     * @api {get} /channels/{id} Details
     * @apiDescription Get the details of the channel with `{id}` identifier.
     * @apiGroup Channels
     * @apiVersion 2.2.0
     * @apiParam {string} include Comma-separated list of what to fetch for every Channel.
     * Available options: `iodevice`, `location`, `connected`, `state`, `supportedFunctions`.
     * @apiParamExample {GET} GET param to fetch Channel's IO Device and location
     * include=iodevice,location
     * @apiSuccess {int} param1 Channel's configuration value. Its meaning depends on the channel's function. Read more about params
     * [on wiki](https://github.com/SUPLA/supla-cloud/wiki/Channel-Functions-parameters).
     * @apiSuccess {int} param2 Channel's configuration value. Its meaning depends on the channel's function. Read more about params
     * [on wiki](https://github.com/SUPLA/supla-cloud/wiki/Channel-Functions-parameters).
     * @apiSuccess {int} param3 Channel's configuration value. Its meaning depends on the channel's function. Read more about params
     * [on wiki](https://github.com/SUPLA/supla-cloud/wiki/Channel-Functions-parameters).
     * @apiSuccess {Object} state Present only if `include=state` has been given. Represents the current channel's state.
     * Read more about `state` format [on wiki](https://github.com/SUPLA/supla-cloud/wiki/Channel-Functions-states).
     * @apiSuccessExample Success
     * {"id":56,"channelNumber":44,"caption":"My sensor","type":{"id":3036,"name":"HUMIDITYSENSOR","caption":"Humidity sensor",
     * "output":false},
     * "function":{"id":42,"name":"HUMIDITY","caption":"Humidity sensor","possibleActions":[],"maxAlternativeIconIndex":0},
     * "funcList":0,"param1":0,"param2":0,"param3":0,"altIcon":0,"hidden":false,"inheritedLocation":true,
     * "iodeviceId":4,"locationId":3,"functionId":42,"typeId":3036}
     * @apiSuccessExample Success with `include=state`
     * {"id":10,"channelNumber":0,"caption":"My RGB",
     * "type":{"id":4010,"name":"RGBLEDCONTROLLER","caption":"RGB led controller","output":true},
     * "function":{"id":200,"name":"DIMMERANDRGBLIGHTING","caption":"Dimmer and RGB lighting",
     * "possibleActions":[{"id":80,"name":"SET_RGBW_PARAMETERS","caption":"Adjust parameters"}],"maxAlternativeIconIndex":0},
     * "funcList":0,"param1":0,"param2":0,"param3":0,"altIcon":0,"hidden":false,"inheritedLocation":true,"iodeviceId":3,"locationId":2,
     * "functionId":200,"typeId":4010,"state":{"color":"0x4674D9","color_brightness":68,"brightness":78}}
     */
    /**
     * @Security("channel.belongsToUser(user)")
     */
    public function getChannelAction(Request $request, IODeviceChannel $channel) {
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            $view = $this->view($channel, Response::HTTP_OK);
            $this->setSerializationGroups(
                $view,
                $request,
                ['iodevice', 'location', 'connected', 'state', 'supportedFunctions']
            );
            return $view;
        } else {
            $enabled = false;
            $connected = false;
            $devid = $channel->getIoDevice()->getId();
            $userid = $this->getUser()->getId();
            if ($channel->getIoDevice()->getEnabled()) {
                $enabled = true;
                $cids = $this->suplaServer->checkDevicesConnection($userid, [$devid]);
                $connected = in_array($devid, $cids);
            }
            $result = array_merge(['connected' => $connected, 'enabled' => $enabled], $this->channelStateGetter->getState($channel));
            return $this->handleView($this->view($result, Response::HTTP_OK));
        }
    }

    /**
     * @Security("channel.belongsToUser(user)")
     */
    public function putChannelAction(Request $request, IODeviceChannel $channel, IODeviceChannel $updatedChannel) {
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            $functionHasBeenChanged = $channel->getFunction() != $updatedChannel->getFunction();
            if ($functionHasBeenChanged) {
                if (!$request->get('confirm') && (count($channel->getSchedules()) || count($channel->getChannelGroups()))) {
                    return $this->view([
                        'schedules' => $channel->getSchedules(),
                        'groups' => $channel->getChannelGroups(),
                    ], Response::HTTP_CONFLICT);
                }
                $channel->setFunction($updatedChannel->getFunction());
            } else {
                $channel->setAltIcon($updatedChannel->getAltIcon());
            }
            if ($updatedChannel->hasInheritedLocation()) {
                $channel->setLocation(null);
            } else {
                $channel->setLocation($updatedChannel->getLocation());
            }
            $channel->setCaption($updatedChannel->getCaption());
            $channel->setHidden($updatedChannel->getHidden());
            $this->channelParamsUpdater->updateChannelParams($channel, $updatedChannel);
            return $this->transactional(function (EntityManagerInterface $em) use ($functionHasBeenChanged, $request, $channel) {
                $em->persist($channel);
                if ($functionHasBeenChanged) {
                    foreach ($channel->getSchedules() as $schedule) {
                        $this->get('schedule_manager')->delete($schedule);
                    }
                    $channel->removeFromAllChannelGroups($em);
                }
                $this->suplaServer->reconnect();
                return $this->getChannelAction($request, $channel);
            });
        } else {
            $data = json_decode($request->getContent(), true);
            $this->channelActionExecutor->executeAction($channel, ChannelFunctionAction::SET_RGBW_PARAMETERS(), $data);
            return $this->handleView($this->view(null, Response::HTTP_OK));
        }
    }

    /**
     * @api {patch} /channels/{id} Execute action
     * @apiDescription Execute action on the channel with `{id}` identifier.
     * @apiGroup Channels
     * @apiVersion 2.2.0
     * @apiParam {string} action Action to execute on the channel. See available actions
     * [on wiki](https://github.com/SUPLA/supla-cloud/wiki/Channel-Actions).
     * @apiExample {turn-on} Example TURN_OFF
     *     {"action": "TURN_ON"}
     * @apiExample {shut} Example SHUT
     *     {"action": "SHUT", "percentage": 80}
     * @apiExample {shut} Example SET_RGBW_PARAMETERS
     *     {"action": "SET_RGBW_PARAMETERS", "color": "0x06FE63", "color_brightness": 20, "brightness": 70}
     * @apiError 400 Bad Request. Check if the chosen action can be executed on the given channel. Response should contain more details.
     */
    /**
     * @Security("channel.belongsToUser(user)")
     */
    public function patchChannelAction(Request $request, IODeviceChannel $channel) {
        $params = json_decode($request->getContent(), true);
        Assertion::keyExists($params, 'action', 'Missing action.');
        $action = ChannelFunctionAction::fromString($params['action']);
        unset($params['action']);
        $this->channelActionExecutor->executeAction($channel, $action, $params);
        return $this->handleView($this->view(null, Response::HTTP_NO_CONTENT));
    }

    /**
     * @Rest\Get("/channels/{channel}/csv")
     * @Security("channel.belongsToUser(user)")
     */
    public function channelItemGetCSVAction(IODeviceChannel $channel) {
        $file = $this->deviceManager->channelGetCSV($channel, "measurement_" . $channel->getId());
        if ($file !== false) {
            return new StreamedResponse(
                function () use ($file) {
                    readfile($file);
                    unlink($file);
                },
                200,
                [
                    'Content-Type' => 'application/zip',
                    'Content-Disposition' => 'attachment; filename="measurement_' . $channel->getId() . '.zip"',
                ]
            );
        }
        return new Response('Error creating file', Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
