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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Entity\ElectricityMeterLogItem;
use SuplaBundle\Entity\ImpulseCounterLogItem;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\TemperatureLogItem;
use SuplaBundle\Entity\TempHumidityLogItem;
use SuplaBundle\Entity\ThermostatLogItem;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\EventListener\UnavailableInMaintenance;
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
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(IODeviceManager $deviceManager, EntityManagerInterface $entityManager) {
        $this->deviceManager = $deviceManager;
        $this->entityManager = $entityManager;
    }

    private function getMeasureLogsCount(IODeviceChannel $channel) {
        $this->ensureChannelHasMeasurementLogs($channel);
        switch ($channel->getFunction()->getId()) {
            case ChannelFunction::HUMIDITYANDTEMPERATURE:
                $repoName = 'TempHumidityLogItem';
                break;
            case ChannelFunction::THERMOMETER:
                $repoName = 'TemperatureLogItem';
                break;
            case ChannelFunction::ELECTRICITYMETER:
                $repoName = 'ElectricityMeterLogItem';
                break;
            case ChannelFunction::IC_ELECTRICITYMETER:
            case ChannelFunction::IC_GASMETER:
            case ChannelFunction::IC_WATERMETER:
            case ChannelFunction::IC_HEATMETER:
                $repoName = 'ImpulseCounterLogItem';
                break;
            case ChannelFunction::THERMOSTAT:
            case ChannelFunction::THERMOSTATHEATPOLHOMEPLUS:
                $repoName = 'ThermostatLogItem';
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

        $stmt = $this->entityManager->getConnection()->prepare($sql);
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

    private function ensureChannelHasMeasurementLogs(IODeviceChannel $channel, ?array $allowedFuncList = null): void {
        if ($allowedFuncList == null) {
            $allowedFuncList = [
                ChannelFunction::HUMIDITYANDTEMPERATURE,
                ChannelFunction::THERMOMETER,
                ChannelFunction::ELECTRICITYMETER,
                ChannelFunction::IC_ELECTRICITYMETER,
                ChannelFunction::IC_GASMETER,
                ChannelFunction::IC_WATERMETER,
                ChannelFunction::IC_HEATMETER,
                ChannelFunction::THERMOSTAT,
                ChannelFunction::THERMOSTATHEATPOLHOMEPLUS,
            ];
        }
        Assertion::inArray(
            $channel->getFunction()->getId(),
            $allowedFuncList,
            'Cannot fetch these measurement logs for channel with function ' . $channel->getFunction()->getName()
        );
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
        $this->ensureChannelHasMeasurementLogs($channel, $allowedFuncList);
        $offset = intval($offset);
        $limit = intval($limit);
        if ($limit <= 0) {
            $limit = self::RECORD_LIMIT_PER_REQUEST;
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
            case ChannelFunction::IC_ELECTRICITYMETER:
            case ChannelFunction::IC_GASMETER:
            case ChannelFunction::IC_WATERMETER:
                $result = $this->logItems(
                    "`supla_ic_log`",
                    "`counter`, `calculated_value` / 1000 calculated_value",
                    $channel->getId(),
                    $offset,
                    $limit,
                    $afterTimestamp,
                    $beforeTimestamp,
                    $orderDesc
                );
                break;
            case ChannelFunction::THERMOSTAT:
            case ChannelFunction::THERMOSTATHEATPOLHOMEPLUS:
                $result = $this->logItems(
                    "`supla_thermostat_log`",
                    "`on`,`measured_temperature`,`preset_temperature`",
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

    private function deleteMeasurementLogs($entityClass, IODeviceChannel $channel) {
        $repo = $this->getDoctrine()->getRepository($entityClass);

        $repo->createQueryBuilder('log')
            ->delete()
            ->where('log.channel_id = :channelId')
            ->setParameters([
                'channelId' => $channel->getId(),
            ])
            ->getQuery()
            ->execute();
    }

    /**
     * @Rest\Delete("/channels/{channel}/measurement-logs")
     * @Security("channel.belongsToUser(user) and has_role('ROLE_CHANNELS_RW') and is_granted('accessIdContains', channel)")
     * @UnavailableInMaintenance
     */
    public function deleteMeasurementLogsAction(Request $request, IODeviceChannel $channel) {

        if (!ApiVersions::V2_3()->isRequestedEqualOrGreaterThan($request)) {
            throw new NotFoundHttpException();
        }

        $this->ensureChannelHasMeasurementLogs($channel);

        $this->deleteMeasurementLogs(ThermostatLogItem::class, $channel);
        $this->deleteMeasurementLogs(ElectricityMeterLogItem::class, $channel);
        $this->deleteMeasurementLogs(ImpulseCounterLogItem::class, $channel);
        $this->deleteMeasurementLogs(TemperatureLogItem::class, $channel);
        $this->deleteMeasurementLogs(TempHumidityLogItem::class, $channel);

        return new Response('', Response::HTTP_NO_CONTENT);
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
