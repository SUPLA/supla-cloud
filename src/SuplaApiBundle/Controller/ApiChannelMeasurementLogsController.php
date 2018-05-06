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
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaApiBundle\Model\ApiVersions;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Model\IODeviceManager;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiChannelMeasurementLogsController extends RestController {
    use SuplaServerAware;
    use Transactional;

    const RECORD_LIMIT_PER_REQUEST = 5000;

    /** @var IODeviceManager */
    private $deviceManager;

    public function __construct(IODeviceManager $deviceManager) {
        $this->deviceManager = $deviceManager;
    }

    private function getMeasureLogsCount(IODeviceChannel $channel) {
        $functionId = $channel->getFunction()->getId();
        Assertion::inArray(
            $functionId,
            [ChannelFunction::HUMIDITYANDTEMPERATURE, ChannelFunction::THERMOMETER],
            'Cannot fetch measurementLogsCount for channel with function ' . $channel->getFunction()->getName()
        );
        $repoName = $functionId == ChannelFunction::HUMIDITYANDTEMPERATURE ? 'TempHumidityLogItem' : 'TemperatureLogItem';
        $rep = $this->entityManager->getRepository('SuplaBundle:' . $repoName);
        $query = $rep->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->where('f.channel_id = :id')
            ->setParameter('id', $channel->getId())
            ->getQuery();
        return intval($query->getSingleScalarResult());
    }

    private function temperatureLogItems($channelid, $offset, $limit, $orderDesc = true) {
        $order = $orderDesc ? ' ORDER BY `date` DESC, id DESC ' : '';
        $sql = "SELECT UNIX_TIMESTAMP(CONVERT_TZ(`date`, '+00:00', 'SYSTEM')) AS date_timestamp, `temperature` ";
        $sql .= "FROM `supla_temperature_log` WHERE channel_id = ? $order LIMIT ? OFFSET ?";
        $stmt = $this->container->get('doctrine')->getManager()->getConnection()->prepare($sql);
        $stmt->bindValue(1, $channelid, 'integer');
        $stmt->bindValue(2, $limit, 'integer');
        $stmt->bindValue(3, $offset, 'integer');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    protected function temperatureAndHumidityLogItems($channelid, $offset, $limit, $orderDesc = true) {
        $order = $orderDesc ? ' ORDER BY `date` DESC, id DESC ' : '';
        $sql = "SELECT UNIX_TIMESTAMP(CONVERT_TZ(`date`, '+00:00', 'SYSTEM')) AS date_timestamp, `temperature`, ";
        $sql .= "`humidity` FROM `supla_temphumidity_log` WHERE channel_id = ? $order LIMIT ? OFFSET ?";
        $stmt = $this->container->get('doctrine')->getManager()->getConnection()->prepare($sql);
        $stmt->bindValue(1, $channelid, 'integer');
        $stmt->bindValue(2, $limit, 'integer');
        $stmt->bindValue(3, $offset, 'integer');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getTempHumidityLogItemsAction($th, IODeviceChannel $channel, $offset, $limit, $orderDesc = true) {
        $f[] = $th === true ? ChannelFunction::HUMIDITYANDTEMPERATURE : ChannelFunction::THERMOMETER;

        Assertion::inArray($channel->getFunction()->getId(), $f, 'The requested function is not available on this channel');

        $offset = intval($offset);
        $limit = intval($limit);

        if ($limit <= 0) {
            $limit = self::RECORD_LIMIT_PER_REQUEST;
        }

        if ($th === true) {
            $result = $this->temperatureAndHumidityLogItems($channel->getId(), $offset, $limit, $orderDesc);
        } else {
            $result = $this->temperatureLogItems($channel->getId(), $offset, $limit, $orderDesc);
        }

        return $result;
    }

    /**
     * @Rest\Get("/channels/{channel}/temperature-log-count", name="_temp_log_count")
     * @Rest\Get("/channels/{channel}/temperature-and-humidity-count", name="_hum_log_count")
     * @Security("channel.belongsToUser(user)")
     */
    public function getObsoleteMeasurementLogsCountAction(IODeviceChannel $channel, Request $request) {
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            throw new NotFoundHttpException();
        }
        $count = $this->getMeasureLogsCount($channel);
        return [
            'count' => $count,
            'record_limit_per_request' => self::RECORD_LIMIT_PER_REQUEST,
        ];
    }

    /**
     * @Rest\Get("/channels/{channel}/temperature-log-items")
     * @Security("channel.belongsToUser(user)")
     */
    public function getTempLogItemsAction(Request $request, IODeviceChannel $channel) {
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            throw new NotFoundHttpException();
        }
        $result = $this->getTempHumidityLogItemsAction(
            false,
            $channel,
            @$request->query->get('offset'),
            @$request->query->get('limit'),
            false
        );
        return $this->handleView($this->view(['log' => $result], Response::HTTP_OK));
    }

    /**
     * @Rest\Get("/channels/{channel}/temperature-and-humidity-items")
     * @Security("channel.belongsToUser(user)")
     */
    public function getTempHumLogItemsAction(Request $request, IODeviceChannel $channel) {
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            throw new NotFoundHttpException();
        }
        $result = $this->getTempHumidityLogItemsAction(
            true,
            $channel,
            @$request->query->get('offset'),
            @$request->query->get('limit'),
            false
        );
        return $this->handleView($this->view(['log' => $result], Response::HTTP_OK));
    }

    /**
     * @Rest\Get("/channels/{channel}/measurement-logs")
     * @Security("channel.belongsToUser(user)")
     */
    public function getMeasurementLogsAction(Request $request, IODeviceChannel $channel) {
        if (!ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            throw new NotFoundHttpException();
        }
        $humidity = $channel->getFunction()->getId() == ChannelFunction::HUMIDITYANDTEMPERATURE;
        $logs = $this->getTempHumidityLogItemsAction(
            $humidity,
            $channel,
            @$request->query->get('offset'),
            @$request->query->get('limit')
        );
        $view = $this->view($logs, Response::HTTP_OK);
        $view->setHeader('X-Total-Count', $this->getMeasureLogsCount($channel));
        return $view;
    }

    /**
     * @Rest\Get("/channels/{channel}/measurement-logs-csv")
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
