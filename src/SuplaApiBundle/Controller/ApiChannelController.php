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
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaApiBundle\Model\ApiVersions;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\IODeviceManager;
use SuplaBundle\Supla\SuplaConst;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiChannelController extends RestController {
    use SuplaServerAware;

    const RECORD_LIMIT_PER_REQUEST = 5000;
    /**
     * @var IODeviceManager
     */
    private $deviceManager;

    public function __construct(IODeviceManager $deviceManager) {
        $this->deviceManager = $deviceManager;
    }

    public function getChannelsAction(Request $request) {
        $criteria = Criteria::create();
        if (($function = $request->get('function')) !== null) {
            $criteria->andWhere(Criteria::expr()->in('function', explode(',', $function)));
        }
        if (($io = $request->get('io')) !== null) {
            Assertion::inArray($io, ['input', 'output']);
            $criteria->andWhere(Criteria::expr()->in('type', $io == 'output' ? ChannelType::outputTypes() : ChannelType::inputTypes()));
        }
        if (($hasFunction = $request->get('hasFunction')) !== null) {
            if ($hasFunction) {
                $criteria->andWhere(Criteria::expr()->neq('function', ChannelFunction::NONE));
            } else {
                $criteria->andWhere(Criteria::expr()->eq('function', ChannelFunction::NONE));
            }
        }
        $channels = $this->getUser()->getChannels()->matching($criteria);
        $view = $this->view($channels, Response::HTTP_OK);
        $this->setSerializationGroups($view, $request, ['iodevice', 'location', 'function', 'type']);
        return $view;
    }

    protected function channelById($channelid, $functions = null, $checkConnected = false, $authorize = false) {

        $channelid = intval($channelid);

        $channel = $this->deviceManager->channelById($channelid, $this->getParentUser());

        if (!($channel instanceof IODeviceChannel)) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'The device channel could not be found');
        }

        if (is_array($functions) && !in_array($channel->getFunction()->getId(), $functions)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'The requested function is not available on this device');
        }

        if ($checkConnected === true) {
            $connected = false;

            $devid = $channel->getIoDevice()->getId();
            $userid = $this->getParentUser()->getId();

            if ($channel->getIoDevice()->getEnabled()) {
                $cids = $this->suplaServer->checkDevicesConnection($userid, [$devid]);
                $connected = in_array($devid, $cids);
            }

            if ($connected === false) {
                throw new HttpException(Response::HTTP_SERVICE_UNAVAILABLE, 'The requested device is not connected');
            }
        }

        if ($authorize === true) {
            $token = $this->container->get('security.token_storage')->getToken()->getToken();
            if (true !== $this->suplaServer->oauthAuthorize($userid, $token)) {
                throw new HttpException(Response::HTTP_UNAUTHORIZED, 'Supla server has rejected the authorization token');
            }
        }

        return $channel;
    }

    protected function getTempHumidityLogCountAction($th, $channelid) {

        $f = [];

        if ($th === true) {
            $f[] = SuplaConst::FNC_HUMIDITYANDTEMPERATURE;
        } else {
            $f[] = SuplaConst::FNC_THERMOMETER;
        }

        $channel = $this->channelById($channelid, $f);

        $em = $this->container->get('doctrine')->getManager();
        $rep = $em->getRepository('SuplaBundle:' . ($th === true ? 'TempHumidityLogItem' : 'TemperatureLogItem'));

        $query = $rep->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->where('f.channel_id = :id')
            ->setParameter('id', $channelid)
            ->getQuery();

        return $this->handleView($this->view(
            ['count' => $query->getSingleScalarResult(),
                'record_limit_per_request' => ApiChannelController::RECORD_LIMIT_PER_REQUEST],
            Response::HTTP_OK
        ));
    }

    /**
     * @Rest\Get("/channels/{channelid}/temperature-log-count")
     */
    public function getTempLogCountAction(Request $request, $channelid) {

        return $this->getTempHumidityLogCountAction(false, $channelid);
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

    protected function getTempHumidityLogItemsAction($th, $channelid, $offset, $limit) {

        $f[] = $th === true ? SuplaConst::FNC_HUMIDITYANDTEMPERATURE : SuplaConst::FNC_THERMOMETER;

        $channel = $this->channelById($channelid, $f);

        $offset = intval($offset);
        $limit = intval($limit);

        if ($limit <= 0) {
            $limit = ApiChannelController::RECORD_LIMIT_PER_REQUEST;
        }

        if ($th === true) {
            $result = $this->temperatureAndHumidityLogItems($channelid, $offset, $limit);
        } else {
            $result = $this->temperatureLogItems($channelid, $offset, $limit);
        }

        return $this->handleView($this->view(['log' => $result], Response::HTTP_OK));
    }

    /**
     * @Rest\Get("/channels/{channelid}/temperature-log-items")
     */
    public function getTempLogItemsAction(Request $request, $channelid) {

        return $this->getTempHumidityLogItemsAction(false, $channelid, @$request->query->get('offset'), @$request->query->get('limit'));
    }

    /**
     * @Rest\Get("/channels/{channelid}/temperature-and-humidity-count")
     */
    public function getTempHumLogCountAction(Request $request, $channelid) {

        return $this->getTempHumidityLogCountAction(true, $channelid);
    }

    /**
     * @Rest\Get("/channels/{channelid}/temperature-and-humidity-items")
     */
    public function getTempHumLogItemsAction(Request $request, $channelid) {

        return $this->getTempHumidityLogItemsAction(true, $channelid, @$request->query->get('offset'), @$request->query->get('limit'));
    }

    /**
     * @Rest\Get("/channels/{channel}")
     * @Security("channel.belongsToUser(user)")
     */
    public function getChannelAction(Request $request, IODeviceChannel $channel) {
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            $view = $this->view($channel, Response::HTTP_OK);
            $this->setSerializationGroups($view, $request, ['iodevice', 'location', 'function', 'type', 'connected', 'state']);
            return $view;
        } else {
            $enabled = false;
            $connected = false;
            $channelid = $channel->getId();
            $devid = $channel->getIoDevice()->getId();
            $userid = $this->getParentUser()->getId();

            if ($channel->getIoDevice()->getEnabled()) {
                $enabled = true;

                $cids = $this->suplaServer->checkDevicesConnection($userid, [$devid]);
                $connected = in_array($devid, $cids);
            }

            $result = ['connected' => $connected,
                'enabled' => $enabled,
            ];

            if ($connected) {
                $func = $channel->getFunction()->getId();

                switch ($func) {
                    case SuplaConst::FNC_POWERSWITCH:
                    case SuplaConst::FNC_LIGHTSWITCH:
                        $value = $this->suplaServer->getCharValue($userid, $devid, $channelid);
                        $result['on'] = $value == '1' ? true : false;

                        break;

                    case SuplaConst::FNC_OPENINGSENSOR_GATEWAY:
                    case SuplaConst::FNC_OPENINGSENSOR_GATE:
                    case SuplaConst::FNC_OPENINGSENSOR_GARAGEDOOR:
                    case SuplaConst::FNC_NOLIQUIDSENSOR:
                    case SuplaConst::FNC_OPENINGSENSOR_DOOR:
                    case SuplaConst::FNC_OPENINGSENSOR_ROLLERSHUTTER:
                        $value = $this->suplaServer->getCharValue($userid, $devid, $channelid);
                        $result['hi'] = $value == '1' ? true : false;

                        break;

                    case SuplaConst::FNC_THERMOMETER:
                    case SuplaConst::FNC_HUMIDITY:
                    case SuplaConst::FNC_HUMIDITYANDTEMPERATURE:
                        if ($func == SuplaConst::FNC_THERMOMETER
                            || $func == SuplaConst::FNC_HUMIDITYANDTEMPERATURE
                        ) {
                            $value = $this->suplaServer->getTemperatureValue($userid, $devid, $channelid);

                            if ($value !== false) {
                                $result['temperature'] = $value;
                            }
                        }

                        if ($func == SuplaConst::FNC_HUMIDITY
                            || $func == SuplaConst::FNC_HUMIDITYANDTEMPERATURE
                        ) {
                            $value = $this->suplaServer->getHumidityValue($userid, $devid, $channelid);

                            if ($value !== false) {
                                $result['humidity'] = $value;
                            }
                        }

                        break;

                    case SuplaConst::FNC_DIMMER:
                    case SuplaConst::FNC_RGBLIGHTING:
                    case SuplaConst::FNC_DIMMERANDRGBLIGHTING:
                        $value = $this->suplaServer->getRgbwValue($userid, $devid, $channelid);

                        if ($value !== false) {
                            if ($func == SuplaConst::FNC_RGBLIGHTING
                                || $func == SuplaConst::FNC_DIMMERANDRGBLIGHTING
                            ) {
                                $result['color'] = $value['color'];
                                $result['color_brightness'] = $value['color_brightness'];
                            }

                            if ($func == SuplaConst::FNC_DIMMER
                                || $func == SuplaConst::FNC_DIMMERANDRGBLIGHTING
                            ) {
                                $result['brightness'] = $value['brightness'];
                            }
                        }

                        break;

                    case SuplaConst::FNC_DISTANCESENSOR:
                        $value = $this->suplaServer->getDistanceValue($userid, $devid, $channelid);

                        if ($value !== false) {
                            $result['distance'] = $value;
                        }

                        break;

                    case SuplaConst::FNC_DEPTHSENSOR:
                        $value = $this->suplaServer->getDistanceValue($userid, $devid, $channelid);

                        if ($value !== false) {
                            $result['depth'] = $value;
                        }

                        break;
                }
            }

            return $this->handleView($this->view($result, Response::HTTP_OK));
        }
    }

    /**
     * @Rest\Put("/channels/{channelid}")
     */
    public function putChannelsAction(Request $request, $channelid) {
        $channel = $this->channelById($channelid, null, true, true);
        $data = json_decode($request->getContent());

        $devid = $channel->getIoDevice()->getId();
        $userid = $this->getParentUser()->getId();

        $func = $channel->getFunction()->getId();

        switch ($func) {
            case SuplaConst::FNC_DIMMER:
            case SuplaConst::FNC_RGBLIGHTING:
            case SuplaConst::FNC_DIMMERANDRGBLIGHTING:
                $color = intval(@$data->color);
                $color_brightness = intval(@$data->color_brightness);
                $brightness = intval(@$data->brightness);

                if ($func == SuplaConst::FNC_RGBLIGHTING
                    || $func == SuplaConst::FNC_DIMMERANDRGBLIGHTING
                ) {
                    if ($color <= 0
                        || $color > 0xffffff
                        || $color_brightness < 0
                        || $color_brightness > 100
                    ) {
                        throw new HttpException(Response::HTTP_BAD_REQUEST);
                    }
                }

                if ($func == SuplaConst::FNC_DIMMER
                    || $func == SuplaConst::FNC_DIMMERANDRGBLIGHTING
                ) {
                    if ($brightness < 0
                        || $brightness > 100
                    ) {
                        throw new HttpException(Response::HTTP_BAD_REQUEST);
                    }
                }

                if (false === $this->suplaServer->setRgbwValue($userid, $devid, $channelid, $color, $color_brightness, $brightness)) {
                    throw new HttpException(Response::HTTP_SERVICE_UNAVAILABLE);
                }

                break;

            default:
                throw new HttpException(Response::HTTP_BAD_REQUEST, 'Invalid action.');
        }

        return $this->handleView($this->view(null, Response::HTTP_OK));
    }

    private function checkPatchAllowed($action, $func) {

        switch ($action) {
            case 'turn-on':
            case 'turn-off':
                switch ($func) {
                    case SuplaConst::FNC_POWERSWITCH:
                    case SuplaConst::FNC_LIGHTSWITCH:
                        return true;
                }
                break;

            case 'open':
                switch ($func) {
                    case SuplaConst::FNC_CONTROLLINGTHEGATEWAYLOCK:
                    case SuplaConst::FNC_CONTROLLINGTHEDOORLOCK:
                        return true;
                }
                break;

            case 'open-close':
                switch ($func) {
                    case SuplaConst::FNC_CONTROLLINGTHEGATE:
                    case SuplaConst::FNC_CONTROLLINGTHEGARAGEDOOR:
                        return true;
                }
                break;

            case 'shut':
            case 'reveal':
            case 'stop':
                if ($func == SuplaConst::FNC_CONTROLLINGTHEROLLERSHUTTER) {
                    return true;
                }

                break;
        }

        throw new HttpException(Response::HTTP_BAD_REQUEST, 'Invalid action.');
    }

    /**
     * @Rest\Patch("/channels/{channelid}")
     */
    public function patchChannelsAction(Request $request, $channelid) {
        $channel = $this->channelById($channelid, null, true, true);
        $data = json_decode($request->getContent());

        $devid = $channel->getIoDevice()->getId();
        $userid = $this->getParentUser()->getId();
        $action = @$data->action;

        $func = $channel->getFunction()->getId();
        $this->checkPatchAllowed($action, $func);

        $value = 0;

        switch ($action) {
            case 'turn-on':
            case 'open':
            case 'open-close':
                $value = 1;
                break;
            case 'shut':
                $value = 1;

                $percent = intval(@$data->percent);

                if ($percent >= 0 && $percent <= 100) {
                    $value = 10 + $percent;
                }

                break;
            case 'reveal':
                $value = 2;

                $percent = intval(@$data->percent);

                if ($percent >= 0 && $percent <= 100) {
                    $value = 110 - $percent;
                }

                break;
        }

        if (false === $this->suplaServer->setCharValue($userid, $devid, $channelid, $value)) {
            throw new HttpException(Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return $this->handleView($this->view(null, Response::HTTP_OK));
    }
}
