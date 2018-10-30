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
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\IODeviceManager;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChannelMeasurementLogsController extends RestController {
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
            [ChannelFunction::HUMIDITYANDTEMPERATURE, ChannelFunction::THERMOMETER, ChannelFunction::ELECTRICITYMETER],
            'Cannot fetch measurementLogsCount for channel with function ' . $channel->getFunction()->getName()
        );

        switch ($channel->getFunction()->getId()) {
            case ChannelFunction::HUMIDITYANDTEMPERATURE:
                $repoName = 'TempHumidityLogItem';
                break;
            case ChannelFunction::THERMOMETER:
                $repoName = 'TemperatureLogItem';
                break;
            case ChannelFunction::ELECTRICITYMETER:
                switch ($channel->getType()->getId()) {
                    case ChannelType::IMPULSECOUNTER:
                        $repoName = 'ImpulseCounterLogItem';
                        break;
                    case ChannelType::ELECTRICITYMETER:
                        $repoName = 'ElectricityMeterLogItem';
                        break;
                }
                break;
            case ChannelFunction::GASMETER:
            case ChannelFunction::WATERMETER:
                $repoName = 'ImpulseCounterLogItem';
                break;
        }

        $rep = $this->entityManager->getRepository('SuplaBundle:' . $repoName);
        $query = $rep->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->where('f.channel_id = :id')
            ->setParameter('id', $channel->getId())
            ->getQuery();
        return intval($query->getSingleScalarResult());
    }

    private function logItems($table, $fields, $channelid, $offset, $limit, $afterTimestamp = 0, $beforeTimestamp = 0, $orderDesc = true) {
        $order = $orderDesc ? ' ORDER BY `date` DESC, id DESC ' : '';
        $sql = "SELECT UNIX_TIMESTAMP(CONVERT_TZ(`date`, '+00:00', 'SYSTEM')) AS date_timestamp, $fields ";
        $sql .= "FROM $table WHERE channel_id = ? ";

        if ($afterTimestamp > 0 || $beforeTimestamp > 0) {
            if ($afterTimestamp > 0) {
                $sql .= "AND UNIX_TIMESTAMP(CONVERT_TZ(`date`, '+00:00', 'SYSTEM'))  > ? ";
            }

            if ($beforeTimestamp > 0) {
                $sql .= "AND UNIX_TIMESTAMP(CONVERT_TZ(`date`, '+00:00', 'SYSTEM'))  < ? ";
            }

            $sql .= "$order LIMIT ?";
        } else {
            $sql .= "$order LIMIT ? OFFSET ?";
        }

        $stmt = $this->container->get('doctrine')->getManager()->getConnection()->prepare($sql);
        $stmt->bindValue(1, $channelid, 'integer');

        if ($afterTimestamp > 0 || $beforeTimestamp > 0) {
            $n = 2;
            if ($afterTimestamp > 0) {
                $stmt->bindValue($n, $afterTimestamp, 'integer');
                $n++;
            }
            if ($beforeTimestamp > 0) {
                $stmt->bindValue($n, $beforeTimestamp, 'integer');
                $n++;
            }
            $stmt->bindValue($n, $limit, 'integer');
        } else {
            $stmt->bindValue(2, $limit, 'integer');
            $stmt->bindValue(3, $offset, 'integer');
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getMeasurementLogItemsAction(
        IODeviceChannel $channel,
        $offset,
        $limit,
        $afterTimestamp = 0,
        $beforeTimestamp = 0,
        $orderDesc = true,
        $allowedFuncList = null
    ) {

        if ($allowedFuncList == null) {
            $allowedFuncList = [ChannelFunction::HUMIDITYANDTEMPERATURE, ChannelFunction::THERMOMETER,
                ChannelFunction::ELECTRICITYMETER, ChannelFunction::GASMETER, ChannelFunction::WATERMETER];
        }

        Assertion::inArray($channel->getFunction()->getId(), $allowedFuncList, 'The requested action is not available on this channel');

        $offset = intval($offset);
        $limit = intval($limit);

        if ($limit <= 0) {
            $limit = self::RECORD_LIMIT_PER_REQUEST;
        }

        if ($channel->getType()->getId() == ChannelType::IMPULSECOUNTER) {
            return $this->logItems(
                "`supla_ic_log`",
                "`counter`, `calculated_value`",
                $channel->getId(),
                $offset,
                $limit,
                $afterTimestamp,
                $beforeTimestamp,
                $orderDesc
            );
        }

        switch ($channel->getFunction()->getId()) {
            case ChannelFunction::HUMIDITYANDTEMPERATURE:
                $result = $this->logItems(
                    "`supla_temphumidity_log`",
                    "`temperature`, `humidity`",
                    $channel->getId(),
                    $offset,
                    $limit,
                    $afterTimestamp,
                    $beforeTimestamp,
                    $orderDesc
                );
                break;
            case ChannelFunction::THERMOMETER:
                $result = $this->logItems(
                    "`supla_temperature_log`",
                    "`temperature`",
                    $channel->getId(),
                    $offset,
                    $limit,
                    $afterTimestamp,
                    $beforeTimestamp,
                    $orderDesc
                );
                break;
            case ChannelFunction::ELECTRICITYMETER:
                $result = $this->logItems(
                    "`supla_em_log`",
                    "`phase1_fae`, `phase1_rae`, `phase1_fre`, "
                    . "`phase1_rre`, `phase2_fae`, `phase2_rae`, `phase2_fre`, `phase2_rre`, `phase3_fae`, "
                    . "`phase3_rae`, `phase3_fre`, `phase3_rre`",
                    $channel->getId(),
                    $offset,
                    $limit,
                    $afterTimestamp,
                    $beforeTimestamp,
                    $orderDesc
                );
                break;
        }

        return $result;
    }

    /**
     * @Rest\Get("/channels/{channel}/temperature-log-count")
     * @Security("channel.belongsToUser(user) and has_role('ROLE_CHANNELS_R') and is_granted('accessIdContains', channel)")
     */
    public function getObsoleteMeasurementTempLogsCountAction(IODeviceChannel $channel, Request $request) {
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            throw new NotFoundHttpException();
        }
        Assertion::eq($channel->getFunction()->getId(), ChannelFunction::THERMOMETER);
        $count = $this->getMeasureLogsCount($channel);
        return [
            'count' => $count,
            'record_limit_per_request' => self::RECORD_LIMIT_PER_REQUEST,
        ];
    }

    /**
     * @Rest\Get("/channels/{channel}/temperature-and-humidity-count")
     * @Security("channel.belongsToUser(user) and has_role('ROLE_CHANNELS_R') and is_granted('accessIdContains', channel)")
     */
    public function getObsoleteMeasurementHumLogsCountAction(IODeviceChannel $channel, Request $request) {
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            throw new NotFoundHttpException();
        }
        Assertion::eq($channel->getFunction()->getId(), ChannelFunction::HUMIDITYANDTEMPERATURE);
        $count = $this->getMeasureLogsCount($channel);
        return [
            'count' => $count,
            'record_limit_per_request' => self::RECORD_LIMIT_PER_REQUEST,
        ];
    }

    /**
     * @Rest\Get("/channels/{channel}/temperature-log-items")
     * @Security("channel.belongsToUser(user) and has_role('ROLE_CHANNELS_R') and is_granted('accessIdContains', channel)")
     */
    public function getTempLogItemsAction(Request $request, IODeviceChannel $channel) {
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            throw new NotFoundHttpException();
        }
        $result = $this->getMeasurementLogItemsAction(
            $channel,
            @$request->query->get('offset'),
            @$request->query->get('limit'),
            0,
            0,
            false,
            [ChannelFunction::THERMOMETER]
        );
        return $this->handleView($this->view(['log' => $result], Response::HTTP_OK));
    }

    /**
     * @Rest\Get("/channels/{channel}/temperature-and-humidity-items")
     * @Security("channel.belongsToUser(user) and has_role('ROLE_CHANNELS_R') and is_granted('accessIdContains', channel)")
     */
    public function getTempHumLogItemsAction(Request $request, IODeviceChannel $channel) {
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            throw new NotFoundHttpException();
        }
        $result = $this->getMeasurementLogItemsAction(
            $channel,
            @$request->query->get('offset'),
            @$request->query->get('limit'),
            0,
            0,
            false,
            [ChannelFunction::HUMIDITYANDTEMPERATURE]
        );
        return $this->handleView($this->view(['log' => $result], Response::HTTP_OK));
    }

    /**
     * @Rest\Get("/channels/{channel}/measurement-logs")
     * @Security("channel.belongsToUser(user) and has_role('ROLE_CHANNELS_R') and is_granted('accessIdContains', channel)")
     */
    public function getMeasurementLogsAction(Request $request, IODeviceChannel $channel) {
        if (!ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            throw new NotFoundHttpException();
        }
        $logs = $this->getMeasurementLogItemsAction(
            $channel,
            @$request->query->get('offset'),
            @$request->query->get('limit'),
            @$request->query->get('afterTimestamp'),
            @$request->query->get('beforeTimestamp'),
            @$request->query->get('order') !== 'ASC'
        );
        $view = $this->view($logs, Response::HTTP_OK);
        $view->setHeader('X-Total-Count', $this->getMeasureLogsCount($channel));
        return $view;
    }

    /**
     * @Rest\Get("/channels/{channel}/measurement-logs-csv")
     * @Security("channel.belongsToUser(user) and has_role('ROLE_CHANNELS_FILES') and is_granted('accessIdContains', channel)")
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
