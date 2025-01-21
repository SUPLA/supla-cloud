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
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\MeasurementLogs\ElectricityMeterCurrentLogItem;
use SuplaBundle\Entity\MeasurementLogs\ElectricityMeterLogItem;
use SuplaBundle\Entity\MeasurementLogs\ElectricityMeterPowerActiveLogItem;
use SuplaBundle\Entity\MeasurementLogs\ElectricityMeterVoltageAberrationLogItem;
use SuplaBundle\Entity\MeasurementLogs\ElectricityMeterVoltageLogItem;
use SuplaBundle\Entity\MeasurementLogs\GeneralPurposeMeasurementLogItem;
use SuplaBundle\Entity\MeasurementLogs\GeneralPurposeMeterLogItem;
use SuplaBundle\Entity\MeasurementLogs\ImpulseCounterLogItem;
use SuplaBundle\Entity\MeasurementLogs\TemperatureLogItem;
use SuplaBundle\Entity\MeasurementLogs\TempHumidityLogItem;
use SuplaBundle\Entity\MeasurementLogs\ThermostatLogItem;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\EventListener\UnavailableInMaintenance;
use SuplaBundle\Exception\ApiException;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\IODeviceManager;
use SuplaBundle\Model\MeasurementCsvExporter;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
use SuplaBundle\Repository\IODeviceChannelRepository;
use SuplaBundle\Supla\SuplaServerAware;
use SuplaBundle\Utils\DatabaseUtils;
use SuplaBundle\Utils\StringUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChannelMeasurementLogsController extends RestController {
    use SuplaServerAware;

    const RECORD_LIMIT_PER_REQUEST = 5000;

    private const LOGS_TYPE_VOLTAGE_ABERRATIONS = 'voltageAberrations';
    private const LOGS_TYPE_VOLTAGE_HISTORY = 'voltageHistory';
    private const LOGS_TYPE_CURRENT_HISTORY = 'currentHistory';
    private const LOGS_TYPE_POWER_ACTIVE_HISTORY = 'powerActiveHistory';

    /** @var IODeviceManager */
    private $deviceManager;
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var IODeviceChannelRepository */
    private $channelRepository;
    /** @var SubjectConfigTranslator */
    private $channelParamConfigTranslator;

    public function __construct(
        IODeviceManager $deviceManager,
        IODeviceChannelRepository $channelRepository,
        $measurementLogsEntityManager,
        SubjectConfigTranslator $channelParamConfigTranslator
    ) {
        $this->deviceManager = $deviceManager;
        $this->entityManager = $measurementLogsEntityManager;
        $this->channelRepository = $channelRepository;
        $this->channelParamConfigTranslator = $channelParamConfigTranslator;
    }

    private function getMeasureLogsCount(
        IODeviceChannel $channel,
        $afterTimestamp = 0,
        $beforeTimestamp = 0,
        string $logsType = ''
    ) {
        $table = $this->getLogsTableName($channel, $logsType);

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

        $result = $stmt->executeQuery();
        return $result->fetchNumeric();
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
    ): array {
        $offset = intval($offset);
        $limit = intval($limit);
        if ($limit <= 0) {
            $limit = self::RECORD_LIMIT_PER_REQUEST;
        }

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
                $limitSql = 'LIMIT ? OFFSET ?';
            }
        } elseif (!$sparse) {
            $limitSql = 'LIMIT ? OFFSET ?';
        }

        DatabaseUtils::turnOffQueryBuffering($this->entityManager);

        if ($sparse > 0) {
            Assertion::between($sparse, 1, 1000, 'Invalid sparse value.');
            $this->entityManager->getConnection()->executeStatement('SET @nth_log_item_row := 0');
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
                $stmt->bindValue($n++, $limit, 'integer');
                $stmt->bindValue($n, $offset, 'integer');
            }
        } elseif (!$sparse) {
            Assertion::between($limit, 0, self::RECORD_LIMIT_PER_REQUEST, 'Invalid limit.');
            $stmt->bindValue(2, $limit, 'integer');
            $stmt->bindValue(3, $offset, 'integer');
        }

        $result = $stmt->executeQuery();
        return $result->fetchAllAssociative();
    }

    private function getLogsTableName(IODeviceChannel $channel, string $logsType): string {
        $this->ensureChannelHasMeasurementLogs($channel);
        $functionDescriptor = $logsType . $channel->getFunction()->getId();
        switch ($functionDescriptor) {
            case ChannelFunction::HUMIDITYANDTEMPERATURE:
            case ChannelFunction::HUMIDITY:
                return 'supla_temphumidity_log';
            case ChannelFunction::THERMOMETER:
                return 'supla_temperature_log';
            case ChannelFunction::ELECTRICITYMETER:
                return 'supla_em_log';
            case self::LOGS_TYPE_VOLTAGE_ABERRATIONS . ChannelFunction::ELECTRICITYMETER:
                return 'supla_em_voltage_aberration_log';
            case self::LOGS_TYPE_VOLTAGE_HISTORY . ChannelFunction::ELECTRICITYMETER:
                return 'supla_em_voltage_log';
            case self::LOGS_TYPE_CURRENT_HISTORY . ChannelFunction::ELECTRICITYMETER:
                return 'supla_em_current_log';
            case self::LOGS_TYPE_POWER_ACTIVE_HISTORY . ChannelFunction::ELECTRICITYMETER:
                return 'supla_em_power_active_log';
            case ChannelFunction::IC_ELECTRICITYMETER:
            case ChannelFunction::IC_GASMETER:
            case ChannelFunction::IC_WATERMETER:
            case ChannelFunction::IC_HEATMETER:
                return 'supla_ic_log';
            case ChannelFunction::THERMOSTAT:
            case ChannelFunction::THERMOSTATHEATPOLHOMEPLUS:
                return 'supla_thermostat_log';
            case ChannelFunction::GENERAL_PURPOSE_MEASUREMENT:
                return 'supla_gp_measurement_log';
            case ChannelFunction::GENERAL_PURPOSE_METER:
                return 'supla_gp_meter_log';
            default:
                throw new ApiException('Invalid function.');
        }
    }

    private function ensureChannelHasMeasurementLogs(IODeviceChannel $channel, ?array $allowedFuncList = null): void {
        if ($allowedFuncList == null) {
            $allowedFuncList = [
                ChannelFunction::HUMIDITYANDTEMPERATURE,
                ChannelFunction::THERMOMETER,
                ChannelFunction::HUMIDITY,
                ChannelFunction::ELECTRICITYMETER,
                ChannelFunction::IC_ELECTRICITYMETER,
                ChannelFunction::IC_GASMETER,
                ChannelFunction::IC_WATERMETER,
                ChannelFunction::IC_HEATMETER,
                ChannelFunction::THERMOSTAT,
                ChannelFunction::THERMOSTATHEATPOLHOMEPLUS,
                ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
                ChannelFunction::GENERAL_PURPOSE_METER,
            ];
        }
        Assertion::inArray(
            $channel->getFunction()->getId(),
            $allowedFuncList,
            'Cannot fetch measurement logs for channel with function ' . $channel->getFunction()->getName()
        );
    }

    private function getMeasurementLogItemsAction(
        IODeviceChannel $channel,
        $offset,
        $limit,
        $afterTimestamp = 0,
        $beforeTimestamp = 0,
        $orderDesc = true,
        $sparse = null,
        string $logsType = ''
    ): array {
        $table = $this->getLogsTableName($channel, $logsType);
        switch ($channel->getFunction()->getId()) {
            case ChannelFunction::HUMIDITYANDTEMPERATURE:
            case ChannelFunction::HUMIDITY:
                return $this->logItems(
                    $table,
                    '`temperature`, `humidity`',
                    $channel,
                    $offset,
                    $limit,
                    $afterTimestamp,
                    $beforeTimestamp,
                    $orderDesc,
                    $sparse
                );
            case ChannelFunction::THERMOMETER:
                return $this->logItems(
                    $table,
                    '`temperature`',
                    $channel,
                    $offset,
                    $limit,
                    $afterTimestamp,
                    $beforeTimestamp,
                    $orderDesc,
                    $sparse
                );
            case ChannelFunction::ELECTRICITYMETER:
                $columns = '`phase1_fae`, `phase1_rae`, `phase1_fre`, '
                    . '`phase1_rre`, `phase2_fae`, `phase2_rae`, `phase2_fre`, `phase2_rre`, `phase3_fae`, '
                    . '`phase3_rae`, `phase3_fre`, `phase3_rre`, `fae_balanced`, `rae_balanced`';
                if ($logsType === self::LOGS_TYPE_VOLTAGE_ABERRATIONS) {
                    $columns = 'phase_no phaseNo, count_total countTotal, count_above countAbove, count_below countBelow, ' .
                        'sec_below secBelow, sec_above secAbove, max_sec_above maxSecAbove, max_sec_below maxSecBelow,' .
                        'min_voltage minVoltage, max_voltage maxVoltage, avg_voltage avgVoltage, measurement_time_sec measurementTimeSec';
                }
                if (in_array($logsType, [
                    self::LOGS_TYPE_VOLTAGE_HISTORY, self::LOGS_TYPE_CURRENT_HISTORY, self::LOGS_TYPE_POWER_ACTIVE_HISTORY,
                ])) {
                    $columns = 'phase_no phaseNo, avg avg, min min, max max';
                }
                return $this->logItems(
                    $table,
                    $columns,
                    $channel,
                    $offset,
                    $limit,
                    $afterTimestamp,
                    $beforeTimestamp,
                    $orderDesc,
                    $sparse
                );
            case ChannelFunction::IC_ELECTRICITYMETER:
            case ChannelFunction::IC_GASMETER:
            case ChannelFunction::IC_WATERMETER:
            case ChannelFunction::IC_HEATMETER:
                return $this->logItems(
                    $table,
                    '`counter`, `calculated_value` / 1000 calculated_value',
                    $channel,
                    $offset,
                    $limit,
                    $afterTimestamp,
                    $beforeTimestamp,
                    $orderDesc,
                    $sparse
                );
            case ChannelFunction::THERMOSTAT:
            case ChannelFunction::THERMOSTATHEATPOLHOMEPLUS:
                return $this->logItems(
                    $table,
                    '`on`,`measured_temperature`,`preset_temperature`',
                    $channel,
                    $offset,
                    $limit,
                    $afterTimestamp,
                    $beforeTimestamp,
                    $orderDesc,
                    $sparse
                );
            case ChannelFunction::GENERAL_PURPOSE_MEASUREMENT:
                return $this->logItems(
                    $table,
                    'open_value, close_value, avg_value, max_value, min_value',
                    $channel,
                    $offset,
                    $limit,
                    $afterTimestamp,
                    $beforeTimestamp,
                    $orderDesc,
                    $sparse
                );
            case ChannelFunction::GENERAL_PURPOSE_METER:
                return $this->logItems(
                    $table,
                    '`value`',
                    $channel,
                    $offset,
                    $limit,
                    $afterTimestamp,
                    $beforeTimestamp,
                    $orderDesc,
                    $sparse
                );
            default:
                throw new ApiException('Invalid function.');
        }
    }

    /**
     * @deprecated
     * @Rest\Get("/channels/{channel}/temperature-log-count")
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_R') and is_granted('accessIdContains', channel)")
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
     * @deprecated
     * @Rest\Get("/channels/{channel}/temperature-and-humidity-count")
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_R') and is_granted('accessIdContains', channel)")
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
     * @deprecated
     * @Rest\Get("/channels/{channel}/temperature-log-items")
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_R') and is_granted('accessIdContains', channel)")
     */
    public function getTempLogItemsAction(Request $request, IODeviceChannel $channel) {
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            throw new NotFoundHttpException();
        }
        $this->ensureChannelHasMeasurementLogs($channel, [ChannelFunction::THERMOMETER]);
        $result = $this->getMeasurementLogItemsAction(
            $channel,
            @$request->query->get('offset'),
            @$request->query->get('limit'),
            0,
            0,
            false
        );
        return $this->handleView($this->view(['log' => $result], Response::HTTP_OK));
    }

    /**
     * @deprecated
     * @Rest\Get("/channels/{channel}/temperature-and-humidity-items")
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_R') and is_granted('accessIdContains', channel)")
     */
    public function getTempHumLogItemsAction(Request $request, IODeviceChannel $channel) {
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            throw new NotFoundHttpException();
        }
        $this->ensureChannelHasMeasurementLogs($channel, [ChannelFunction::HUMIDITYANDTEMPERATURE]);
        $result = $this->getMeasurementLogItemsAction(
            $channel,
            @$request->query->get('offset'),
            @$request->query->get('limit'),
            0,
            0,
            false
        );
        return $this->handleView($this->view(['log' => $result], Response::HTTP_OK));
    }

    /**
     * @OA\Get(
     *     path="/channels/{channel}/measurement-logs", operationId="getChannelMeasurementLogs",
     *     summary="Get channel measurement logs.", tags={"Channels"},
     *     @OA\Parameter(name="channel", description="ID", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="afterTimestamp", description="Fetch log items created after this timestamp.", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="beforeTimestamp", description="Fetch log items created before this timestamp.", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="order", description="Whether to order items ascending or descending by creation date.", in="query", @OA\Schema(type="string", default="DESC", enum={"ASC", "DESC"})),
     *     @OA\Parameter(name="sparse", description="Set the maximum items to return from the given period. If specified, the `limit` and `offset` params are ignored. For example, if you fetches the logs from the whole year and set the `sparse` param to `12`, the API will try to return up to 12 log items, equally distributed throug the whole year. Min: 1, Max: 1000.", in="query", @OA\Schema(type="integer", minimum=1, maximum=1000)),
     *     @OA\Parameter(name="logsType", description="Type of the logs to return. Some devices may gather multiple log types.", in="query", @OA\Schema(type="string", enum={"default", "voltageHistory", "powerActiveHistory", "currentHistory", "voltageAberrations"})),
     *     @OA\Parameter(name="limit", description="Maximum items count in response, from 1 to 5000.", in="query", @OA\Schema(type="integer", default=5000, minimum=1, maximum=5000)),
     *     @OA\Parameter(name="offset", description="Pagination offset.", in="query", @OA\Schema(type="integer", default=0)),
     *     @OA\Response(response="200", description="Success",
     *       headers={
     *         @OA\Header(header="X-Total-Count", description="Total count of logs for this channel.", @OA\Schema(type="integer")),
     *         @OA\Header(header="X-Count", description="Total count of logs for this channel, considering the supplied filters.", @OA\Schema(type="integer")),
     *         @OA\Header(header="X-Min-Timestamp", description="Minimum timestamp of the log items, considering the supplied filters.", @OA\Schema(type="integer")),
     *         @OA\Header(header="X-Max-Timestamp", description="Maximum timestamp of the log items, considering the supplied filters.", @OA\Schema(type="integer")),
     *       },
     *       @OA\JsonContent(oneOf={
     *          @OA\Schema(type="array", description="Log item for `THERMOMETER`.", @OA\Items(
     *            @OA\Property(property="date_timestamp", type="integer"),
     *            @OA\Property(property="temperature", type="number", format="float"),
     *          )),
     *          @OA\Schema(type="array", description="Log item for `HUMIDITYANDTEMPERATURE`.", @OA\Items(
     *            @OA\Property(property="date_timestamp", type="integer"),
     *            @OA\Property(property="humidity", type="number", format="float"),
     *            @OA\Property(property="temperature", type="number", format="float"),
     *          )),
     *          @OA\Schema(type="array", description="Log item for `HUMIDITY`.", @OA\Items(
     *            @OA\Property(property="date_timestamp", type="integer"),
     *            @OA\Property(property="humidity", type="number", format="float"),
     *          )),
     *          @OA\Schema(type="array", description="Log item for `ELECTRICITYMETER`.", @OA\Items(
     *            @OA\Property(property="date_timestamp", type="integer"),
     *            @OA\Property(property="phase1_fae", type="integer", nullable=true),
     *            @OA\Property(property="phase1_rae", type="integer", nullable=true),
     *            @OA\Property(property="phase1_fre", type="integer", nullable=true),
     *            @OA\Property(property="phase1_rre", type="integer", nullable=true),
     *            @OA\Property(property="phase2_fae", type="integer", nullable=true),
     *            @OA\Property(property="phase2_rae", type="integer", nullable=true),
     *            @OA\Property(property="phase2_fre", type="integer", nullable=true),
     *            @OA\Property(property="phase2_rre", type="integer", nullable=true),
     *            @OA\Property(property="phase3_fae", type="integer", nullable=true),
     *            @OA\Property(property="phase3_rae", type="integer", nullable=true),
     *            @OA\Property(property="phase3_fre", type="integer", nullable=true),
     *            @OA\Property(property="phase3_rre", type="integer", nullable=true),
     *            @OA\Property(property="fae_balanced", type="integer", nullable=true),
     *            @OA\Property(property="rae_balanced", type="integer", nullable=true),
     *          )),
     *          @OA\Schema(type="array", description="Log item for `ELECTRICITYMETER` voltage log.", @OA\Items(
     *            @OA\Property(property="date_timestamp", type="integer"),
     *            @OA\Property(property="phaseNo", type="integer"),
     *            @OA\Property(property="countTotal", type="integer"),
     *            @OA\Property(property="countAbove", type="integer"),
     *            @OA\Property(property="countBelow", type="integer"),
     *            @OA\Property(property="secAbove", type="integer"),
     *            @OA\Property(property="secBelow", type="integer"),
     *            @OA\Property(property="maxSecAbove", type="integer"),
     *            @OA\Property(property="maxSecBelow", type="integer"),
     *            @OA\Property(property="measurementTimeSec", type="integer"),
     *            @OA\Property(property="avgVoltage", type="number"),
     *            @OA\Property(property="minVoltage", type="number"),
     *            @OA\Property(property="maxVoltage", type="number"),
     *          )),
     *          @OA\Schema(type="array", description="Log item for impulse counters (`IC_*`).", @OA\Items(
     *            @OA\Property(property="date_timestamp", type="integer"),
     *            @OA\Property(property="counter", type="integer"),
     *            @OA\Property(property="calculated_value", type="number", format="float"),
     *          )),
     *          @OA\Schema(type="array", description="Log item for thermostats (`THERMOSTAT*`).", @OA\Items(
     *            @OA\Property(property="date_timestamp", type="integer"),
     *            @OA\Property(property="on", type="boolean"),
     *            @OA\Property(property="measured_temperature", type="number", format="float"),
     *            @OA\Property(property="preset_temperature", type="number", format="float"),
     *          )),
     *       }),
     *     ),
     *     @OA\Response(response="400", description="Unsupported function", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @Rest\Get("/channels/{channel}/measurement-logs")
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_R') and is_granted('accessIdContains', channel)")
     */
    public function getMeasurementLogsAction(Request $request, IODeviceChannel $channel) {
        if (!ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            throw new NotFoundHttpException();
        }
        $minTimestampParam = $request->query->get('afterTimestamp');
        $maxTimestampParam = $request->query->get('beforeTimestamp');
        $channel = $this->findTargetChannel($channel);
        if (!ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($request)) {
            $this->entityManager->getConnection()->getNativeConnection()->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, true);
        }
        $logs = $this->getMeasurementLogItemsAction(
            $channel,
            $request->query->get('offset'),
            $request->query->get('limit'),
            $minTimestampParam,
            $maxTimestampParam,
            $request->query->get('order') !== 'ASC',
            $request->query->get('sparse'),
            $this->getLogsType($request),
        );
        if (ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($request)) {
            $logs = $this->adjustLogsFormat($logs, $channel);
        }
        $view = $this->view($logs, Response::HTTP_OK);
        [$totalCountWithCondition, $minTimestamp, $maxTimestamp] = $this->getMeasureLogsCount(
            $channel,
            $minTimestampParam,
            $maxTimestampParam,
            $this->getLogsType($request)
        );
        if ($minTimestampParam || $maxTimestampParam) {
            [$totalCount,] = $this->getMeasureLogsCount($channel, 0, 0, $this->getLogsType($request));
        } else {
            $totalCount = $totalCountWithCondition;
        }
        $view->setHeader('X-Total-Count', $totalCount);
        $view->setHeader('X-Count', $totalCountWithCondition);
        $view->setHeader('X-Min-Timestamp', $minTimestamp);
        $view->setHeader('X-Max-Timestamp', $maxTimestamp);
        return $view;
    }

    private function adjustLogsFormat(array $logs, IODeviceChannel $channel): array {
        switch ($channel->getFunction()->getId()) {
            case ChannelFunction::HUMIDITYANDTEMPERATURE:
                return array_map(function (array $item) {
                    return [
                        'date_timestamp' => intval($item['date_timestamp']),
                        'temperature' => floatval($item['temperature']),
                        'humidity' => floatval($item['humidity']),
                    ];
                }, $logs);
            case ChannelFunction::HUMIDITY:
                return array_map(function (array $item) {
                    return [
                        'date_timestamp' => intval($item['date_timestamp']),
                        'humidity' => floatval($item['humidity']),
                    ];
                }, $logs);
            case ChannelFunction::THERMOMETER:
                return array_map(function (array $item) {
                    return [
                        'date_timestamp' => intval($item['date_timestamp']),
                        'temperature' => floatval($item['temperature']),
                    ];
                }, $logs);
            case ChannelFunction::ELECTRICITYMETER:
                return array_map(function (array $item) {
                    array_walk($item, function (&$value, string $field) {
                        if (in_array($field, ['minVoltage', 'maxVoltage', 'avgVoltage', 'min', 'max', 'avg'])) {
                            $value = floatval($value);
                        } else {
                            $value = is_numeric($value) ? intval($value) : $value;
                        }
                    });
                    return $item;
                }, $logs);
            case ChannelFunction::IC_ELECTRICITYMETER:
            case ChannelFunction::IC_GASMETER:
            case ChannelFunction::IC_WATERMETER:
            case ChannelFunction::IC_HEATMETER:
                return array_map(function (array $item) {
                    return [
                        'date_timestamp' => intval($item['date_timestamp']),
                        'counter' => floatval($item['counter']),
                        'calculated_value' => floatval($item['calculated_value']),
                    ];
                }, $logs);
            case ChannelFunction::GENERAL_PURPOSE_METER:
                return array_map(function (array $item) {
                    return [
                        'date_timestamp' => intval($item['date_timestamp']),
                        'value' => floatval($item['value']),
                    ];
                }, $logs);
            case ChannelFunction::THERMOSTAT:
            case ChannelFunction::THERMOSTATHEATPOLHOMEPLUS:
                return array_map(function (array $item) {
                    return [
                        'date_timestamp' => intval($item['date_timestamp']),
                        'on' => boolval($item['on']),
                        'measured_temperature' => floatval($item['measured_temperature']),
                        'preset_temperature' => floatval($item['preset_temperature']),
                    ];
                }, $logs);
            case ChannelFunction::GENERAL_PURPOSE_MEASUREMENT:
                return array_map(function (array $item) {
                    return [
                        'date_timestamp' => intval($item['date_timestamp']),
                        'open_value' => floatval($item['open_value']),
                        'close_value' => floatval($item['close_value']),
                        'avg_value' => floatval($item['avg_value']),
                        'min_value' => floatval($item['min_value']),
                        'max_value' => floatval($item['max_value']),
                    ];
                }, $logs);
            default:
                return $logs;
        }
    }

    private function findTargetChannel(IODeviceChannel $channel): IODeviceChannel {
        $targetChannel = $channel;
        if (in_array($channel->getFunction()->getId(), [
            ChannelFunction::POWERSWITCH,
            ChannelFunction::LIGHTSWITCH,
            ChannelFunction::STAIRCASETIMER,
        ])) {
            $channelConfig = $this->channelParamConfigTranslator->getConfig($channel);
            $relatedMeasurementChannelId = $channelConfig['relatedMeterChannelId'] ?? null;
            if ($relatedMeasurementChannelId) {
                $targetChannel = $this->channelRepository->findForUser($channel->getUser(), $relatedMeasurementChannelId);
            }
        }
        return $targetChannel ?: $channel;
    }

    private function deleteMeasurementLogs($entityClass, IODeviceChannel $channel, callable $filters = null) {
        $repo = $this->getDoctrine()->getRepository($entityClass);
        $qb = $repo->createQueryBuilder('log')
            ->delete()
            ->where('log.channel_id = :channelId')
            ->setParameters([
                'channelId' => $channel->getId(),
            ]);
        if ($filters) {
            $qb = $filters($qb);
        }
        $qb->getQuery()
            ->execute();
    }

    /**
     * @OA\Delete(
     *     path="/channels/{channel}/measurement-logs", operationId="deleteChannelMeasurementLogs",
     *     summary="Delete channel measurement logs.", tags={"Channels"},
     *     @OA\Parameter(description="ID", in="path", name="channel", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="logsType", description="Type of the logs to delete. Some devices may gather multiple log types.", in="query", @OA\Schema(type="string", enum={"default", "voltageHistory", "powerActiveHistory", "currentHistory", "voltageAberrations"})),
     *     @OA\Response(response="204", description="Success"),
     *     @OA\Response(response="400", description="Unsupported function", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @Rest\Delete("/channels/{channel}/measurement-logs")
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_RW') and is_granted('accessIdContains', channel)")
     * @UnavailableInMaintenance
     */
    public function deleteMeasurementLogsAction(Request $request, IODeviceChannel $channel) {
        if (!ApiVersions::V2_3()->isRequestedEqualOrGreaterThan($request)) {
            throw new NotFoundHttpException();
        }
        $this->ensureChannelHasMeasurementLogs($channel);
        $logsType = $this->getLogsType($request);
        if ($logsType === self::LOGS_TYPE_VOLTAGE_ABERRATIONS) {
            $this->deleteMeasurementLogs(ElectricityMeterVoltageAberrationLogItem::class, $channel, function (QueryBuilder $qb) use (
                $request
            ) {
                if (in_array($phaseNo = $request->get('phase'), [1, 2, 3])) {
                    $qb->andWhere('log.phaseNo = :phaseNo')->setParameter('phaseNo', $phaseNo);
                }
                return $qb;
            });
        } else {
            $this->deleteMeasurementLogs(ThermostatLogItem::class, $channel);
            $this->deleteMeasurementLogs(ElectricityMeterLogItem::class, $channel);
            $this->deleteMeasurementLogs(ImpulseCounterLogItem::class, $channel);
            $this->deleteMeasurementLogs(TemperatureLogItem::class, $channel);
            $this->deleteMeasurementLogs(TempHumidityLogItem::class, $channel);
            $this->deleteMeasurementLogs(GeneralPurposeMeasurementLogItem::class, $channel);
            $this->deleteMeasurementLogs(GeneralPurposeMeterLogItem::class, $channel);
            $this->deleteMeasurementLogs(ElectricityMeterVoltageLogItem::class, $channel);
            $this->deleteMeasurementLogs(ElectricityMeterCurrentLogItem::class, $channel);
            $this->deleteMeasurementLogs(ElectricityMeterPowerActiveLogItem::class, $channel);
        }
        return new Response('', Response::HTTP_NO_CONTENT);
    }

    private function getLogsType(Request $request): string {
        $logsType = $request->query->get('logsType', 'default');
        if ($logsType === 'default') {
            return '';
        } elseif ($logsType === 'voltage') {
            return self::LOGS_TYPE_VOLTAGE_ABERRATIONS;
        } else {
            Assertion::inArray($logsType, [
                self::LOGS_TYPE_VOLTAGE_ABERRATIONS,
                self::LOGS_TYPE_VOLTAGE_HISTORY,
                self::LOGS_TYPE_CURRENT_HISTORY,
                self::LOGS_TYPE_POWER_ACTIVE_HISTORY,
            ]);
            return $logsType;
        }
    }

    /**
     * @OA\Get(
     *     path="/channels/{channel}/measurement-logs-download", operationId="downloadChannelMeasurementLogs",
     *     summary="Get measurement logs as a zipped CSV file.", tags={"Channels"},
     *     @OA\Parameter(description="ID", in="path", name="channel", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="logsType", description="Type of the logs to delete. Some devices may gather multiple log types.", in="query", @OA\Schema(type="string", enum={"default", "voltageAberrations", "voltageHistory", "powerActiveHistory", "currentHistory"})),
     *     @OA\Response(response="200", description="Success", @OA\MediaType(mediaType="application/zip")),
     *     @OA\Response(response="400", description="Unsupported function", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @Rest\Get("/channels/{channel}/measurement-logs-csv")
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_FILES') and is_granted('accessIdContains', channel)")
     */
    public function channelItemGetCSVAction(Request $request, IODeviceChannel $channel, MeasurementCsvExporter $measurementCsvExporter) {
        $logsType = $this->getLogsType($request);
        $filePath = $measurementCsvExporter->createZipArchiveWithLogs($channel, $logsType);
        $prefix = StringUtils::camelCaseToSnakeCaseLower($logsType ?: 'measurement') . '_';
        return new StreamedResponse(
            function () use ($filePath) {
                readfile($filePath);
                unlink($filePath);
            },
            200,
            [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => 'attachment; filename="' . $prefix . $channel->getId() . '.zip"',
            ]
        );
    }
}
