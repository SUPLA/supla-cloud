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
use PDO;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Entity\ElectricityMeterLogItem;
use SuplaBundle\Entity\ImpulseCounterLogItem;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\TemperatureLogItem;
use SuplaBundle\Entity\TempHumidityLogItem;
use SuplaBundle\Entity\ThermostatLogItem;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\EventListener\UnavailableInMaintenance;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\IODeviceManager;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Repository\IODeviceChannelRepository;
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
    /** @var IODeviceChannelRepository */
    private $channelRepository;

    public function __construct(
        IODeviceManager $deviceManager,
        IODeviceChannelRepository $channelRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->deviceManager = $deviceManager;
        $this->entityManager = $entityManager;
        $this->channelRepository = $channelRepository;
    }

    private function getMeasureLogsCount(IODeviceChannel $channel, $afterTimestamp = 0, $beforeTimestamp = 0) {
        $functionId = $channel->getFunction()->getId();
        Assertion::inArray(
            $functionId,
            [
                ChannelFunction::HUMIDITYANDTEMPERATURE, ChannelFunction::THERMOMETER,
                ChannelFunction::ELECTRICITYMETER, ChannelFunction::GASMETER, ChannelFunction::WATERMETER,
                ChannelFunction::HEATMETER, ChannelFunction::THERMOSTAT, ChannelFunction::THERMOSTATHEATPOLHOMEPLUS,
            ],
            'Cannot fetch measurementLogsCount for channel with function ' . $channel->getFunction()->getName()
        );

        switch ($channel->getFunction()->getId()) {
            case ChannelFunction::HUMIDITYANDTEMPERATURE:
                $table = 'supla_temphumidity_log';
                break;
            case ChannelFunction::THERMOMETER:
                $table = 'supla_temperature_log';
                break;
            case ChannelFunction::ELECTRICITYMETER:
                switch ($channel->getType()->getId()) {
                    case ChannelType::IMPULSECOUNTER:
                        $table = 'supla_ic_log';
                        break;
                    case ChannelType::ELECTRICITYMETER:
                        $table = 'supla_em_log';
                        break;
                }
                break;
            case ChannelFunction::GASMETER:
            case ChannelFunction::WATERMETER:
            case ChannelFunction::HEATMETER:
                $table = 'supla_ic_log';
                break;
            case ChannelFunction::THERMOSTAT:
            case ChannelFunction::THERMOSTATHEATPOLHOMEPLUS:
                $table = 'supla_thermostat_log';
                break;
        }

        $unixDate = "UNIX_TIMESTAMP(CONVERT_TZ(`date`, '+00:00', 'SYSTEM'))";
        $sql = "SELECT COUNT(*), MIN($unixDate), MAX($unixDate) FROM `$table` WHERE channel_id = ? ";

        if ($afterTimestamp > 0 || $beforeTimestamp > 0) {
            if ($afterTimestamp > 0) {
                $sql .= "AND $unixDate > ? ";
            }
            if ($beforeTimestamp > 0) {
                $sql .= "AND $unixDate < ? ";
            }
        }

        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->bindValue(1, $channel->getId(), 'integer');

        if ($afterTimestamp > 0 || $beforeTimestamp > 0) {
            $n = 2;
            if ($afterTimestamp > 0) {
                $stmt->bindValue($n, $afterTimestamp, 'integer');
                $n++;
            }
            if ($beforeTimestamp > 0) {
                $stmt->bindValue($n, $beforeTimestamp, 'integer');
            }
        }

        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_NUM);
        return $stmt->fetch();
    }

    private function logItems(
        $table,
        $fields,
        IODeviceChannel $channel,
        $offset,
        $limit,
        $afterTimestamp = 0,
        $beforeTimestamp = 0,
        $orderDesc = true,
        $sparse = null
    ) {
        $order = $orderDesc ? ' ORDER BY `date` DESC ' : ' ORDER BY `date` ASC ';
        $sql = "SELECT UNIX_TIMESTAMP(CONVERT_TZ(`date`, '+00:00', 'SYSTEM')) AS date_timestamp, $fields ";
        $sql .= "FROM $table WHERE channel_id = ? ";
        $limitSql = '';

        if ($afterTimestamp > 0 || $beforeTimestamp > 0) {
            if ($afterTimestamp > 0) {
                $sql .= "AND UNIX_TIMESTAMP(CONVERT_TZ(`date`, '+00:00', 'SYSTEM')) > ? ";
            }

            if ($beforeTimestamp > 0) {
                $sql .= "AND UNIX_TIMESTAMP(CONVERT_TZ(`date`, '+00:00', 'SYSTEM')) < ? ";
            }
            if (!$sparse) {
                $limitSql = 'LIMIT ?';
            }
        } elseif (!$sparse) {
            $limitSql = 'LIMIT ? OFFSET ?';
        }

        if ($sparse > 0) {
            $this->entityManager->getConnection()->exec('SET @nth_log_item_row := 0');
            [$totalCount,] = $this->getMeasureLogsCount($channel, $afterTimestamp, $beforeTimestamp);
            if ($totalCount > $sparse) {
                $nth = floor($totalCount / $sparse);
                $sql .= "AND (@nth_log_item_row := @nth_log_item_row + 1) % $nth = 0";
            }
        }

        $sql .= "$order $limitSql";

        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->bindValue(1, $channel->getId(), 'integer');

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
            if (!$sparse) {
                $stmt->bindValue($n, $limit, 'integer');
            }
        } elseif (!$sparse) {
            Assertion::between($limit, 0, self::RECORD_LIMIT_PER_REQUEST, 'Invalid limit.');
            $stmt->bindValue(2, $limit, 'integer');
            $stmt->bindValue(3, $offset, 'integer');
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function ensureChannelHasMeasurementLogs(IODeviceChannel $channel, $allowedFuncList = null) {
        if ($allowedFuncList == null) {
            $allowedFuncList = [ChannelFunction::HUMIDITYANDTEMPERATURE, ChannelFunction::THERMOMETER,
                ChannelFunction::ELECTRICITYMETER, ChannelFunction::GASMETER, ChannelFunction::WATERMETER,
                ChannelFunction::HEATMETER, ChannelFunction::THERMOSTAT, ChannelFunction::THERMOSTATHEATPOLHOMEPLUS];
        }

        Assertion::inArray($channel->getFunction()->getId(), $allowedFuncList, 'The requested action is not available on this channel');
    }

    private function getMeasurementLogItemsAction(
        IODeviceChannel $channel,
        $offset,
        $limit,
        $afterTimestamp = 0,
        $beforeTimestamp = 0,
        $orderDesc = true,
        $allowedFuncList = null,
        $sparse = null
    ) {

        $this->ensureChannelHasMeasurementLogs($channel, $allowedFuncList);

        $offset = intval($offset);
        $limit = intval($limit);

        if ($limit <= 0) {
            $limit = self::RECORD_LIMIT_PER_REQUEST;
        }

        if ($channel->getType()->getId() == ChannelType::IMPULSECOUNTER) {
            return $this->logItems(
                "`supla_ic_log`",
                "`counter`, `calculated_value` / 1000 calculated_value",
                $channel,
                $offset,
                $limit,
                $afterTimestamp,
                $beforeTimestamp,
                $orderDesc,
                $sparse
            );
        }

        switch ($channel->getFunction()->getId()) {
            case ChannelFunction::HUMIDITYANDTEMPERATURE:
                $result = $this->logItems(
                    "`supla_temphumidity_log`",
                    "`temperature`, `humidity`",
                    $channel,
                    $offset,
                    $limit,
                    $afterTimestamp,
                    $beforeTimestamp,
                    $orderDesc,
                    $sparse
                );
                break;
            case ChannelFunction::THERMOMETER:
                $result = $this->logItems(
                    "`supla_temperature_log`",
                    "`temperature`",
                    $channel,
                    $offset,
                    $limit,
                    $afterTimestamp,
                    $beforeTimestamp,
                    $orderDesc,
                    $sparse
                );
                break;
            case ChannelFunction::ELECTRICITYMETER:
                $result = $this->logItems(
                    "`supla_em_log`",
                    "`phase1_fae`, `phase1_rae`, `phase1_fre`, "
                    . "`phase1_rre`, `phase2_fae`, `phase2_rae`, `phase2_fre`, `phase2_rre`, `phase3_fae`, "
                    . "`phase3_rae`, `phase3_fre`, `phase3_rre`, `fae_balanced`, `rae_balanced`",
                    $channel,
                    $offset,
                    $limit,
                    $afterTimestamp,
                    $beforeTimestamp,
                    $orderDesc,
                    $sparse
                );
                break;
            case ChannelFunction::THERMOSTAT:
            case ChannelFunction::THERMOSTATHEATPOLHOMEPLUS:
                $result = $this->logItems(
                    "`supla_thermostat_log`",
                    "`on`,`measured_temperature`,`preset_temperature`",
                    $channel,
                    $offset,
                    $limit,
                    $afterTimestamp,
                    $beforeTimestamp,
                    $orderDesc,
                    $sparse
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
        [$count,] = $this->getMeasureLogsCount($channel);
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
        [$count,] = $this->getMeasureLogsCount($channel);
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
        $minTimestampParam = $request->query->get('afterTimestamp');
        $maxTimestampParam = $request->query->get('beforeTimestamp');
        $channel = $this->findTargetChannel($channel);
        $logs = $this->getMeasurementLogItemsAction(
            $channel,
            $request->query->get('offset'),
            $request->query->get('limit'),
            $minTimestampParam,
            $maxTimestampParam,
            $request->query->get('order') !== 'ASC',
            null,
            $request->query->get('sparse')
        );
        $view = $this->view($logs, Response::HTTP_OK);
        [$totalCountWithCondition, $minTimestamp, $maxTimestamp] = $this->getMeasureLogsCount(
            $channel,
            $minTimestampParam,
            $maxTimestampParam
        );
        if ($minTimestampParam || $maxTimestampParam) {
            [$totalCount,] = $this->getMeasureLogsCount($channel);
        } else {
            $totalCount = $totalCountWithCondition;
        }
        $view->setHeader('X-Total-Count', $totalCount);
        $view->setHeader('X-Count', $totalCountWithCondition);
        $view->setHeader('X-Min-Timestamp', $minTimestamp);
        $view->setHeader('X-Max-Timestamp', $maxTimestamp);
        return $view;
    }

    private function findTargetChannel(IODeviceChannel $channel): IODeviceChannel {
        $targetChannel = $channel;
        if (in_array($channel->getFunction()->getId(), [ChannelFunction::POWERSWITCH, ChannelFunction::LIGHTSWITCH])) {
            $relatedMeasurementChannelId = $channel->getParam1();
            if ($relatedMeasurementChannelId) {
                $targetChannel = $this->channelRepository->findForUser($channel->getUser(), $relatedMeasurementChannelId);
            }
        }
        return $targetChannel ?: $channel;
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
