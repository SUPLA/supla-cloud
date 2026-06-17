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

namespace App\Controller\Api;

use App\Entity\Main\IODeviceChannel;
use App\Entity\MeasurementLogs\EnergyTariff;
use App\Entity\MeasurementLogs\EnergyTariffAssignment;
use App\Entity\MeasurementLogs\EnergyTariffPriceList;
use App\Entity\MeasurementLogs\EnergyTariffPriceListAssignment;
use App\Entity\MeasurementLogs\EnergyTariffPriceListItem;
use App\Enums\ChannelFunction;
use App\Model\ApiVersions;
use App\Utils\DatabaseUtils;
use App\Utils\DateUtils;
use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EnergyTariffController extends RestController {
    private const RECORD_LIMIT_PER_REQUEST = 10000;

    public function __construct(private readonly EntityManagerInterface $measurementLogsEntityManager) {
    }

    /**
     * @Security("is_granted('ROLE_CHANNELS_R')")
     * @Rest\Get("/energy-tariffs")
     */
    public function getEnergyTariffsAction(Request $request) {
        $this->ensureApiVersion24($request);
        $tariffs = $this->getMeasurementLogsEntityManager()->getRepository(EnergyTariff::class)->findAll();
        return $this->view(array_map(fn(EnergyTariff $tariff) => $this->serializeTariff($tariff), $tariffs));
    }

    /**
     * @Security("is_granted('ROLE_CHANNELS_R')")
     * @Rest\Get("/energy-tariffs/{tariffId}/price-lists")
     */
    public function getEnergyTariffPriceListsAction(int $tariffId, Request $request) {
        $this->ensureApiVersion24($request);
        $this->findTariffOrThrow($tariffId);
        $priceLists = $this->getMeasurementLogsEntityManager()->getRepository(EnergyTariffPriceList::class)->findBy([
            'tariff' => $tariffId,
            'userId' => $this->getCurrentUserOrThrow()->getId(),
        ]);
        return $this->view(array_map(fn(EnergyTariffPriceList $priceList) => $this->serializePriceList($priceList), $priceLists));
    }

    /**
     * @Security("is_granted('ROLE_CHANNELS_RW')")
     * @Rest\Post("/energy-tariffs/{tariffId}/price-lists")
     */
    public function postEnergyTariffPriceListAction(int $tariffId, Request $request) {
        $this->ensureApiVersion24($request);
        $tariff = $this->findTariffOrThrow($tariffId);
        $data = $this->getRequestData($request);
        Assertion::keyExists($data, 'name');

        $priceList = new EnergyTariffPriceList();
        $priceList->setTariff($tariff);
        $priceList->setUserId($this->getCurrentUserOrThrow()->getId());
        $priceList->setName($data['name']);
        $priceList->setBillingPeriodStartDay((int)($data['billingPeriodStartDay'] ?? 1));
        $this->synchronizePriceListItems($priceList, $data['items'] ?? []);
        $this->getMeasurementLogsEntityManager()->persist($priceList);
        $this->getMeasurementLogsEntityManager()->flush();

        return $this->view($this->serializePriceList($priceList), Response::HTTP_CREATED);
    }

    /**
     * @Security("is_granted('ROLE_CHANNELS_R')")
     * @Rest\Get("/energy-tariffs/{tariffId}/price-lists/{priceListId}")
     */
    public function getEnergyTariffPriceListAction(int $tariffId, int $priceListId, Request $request) {
        $this->ensureApiVersion24($request);
        return $this->view($this->serializePriceList($this->findOwnedPriceListOrThrow($tariffId, $priceListId)));
    }

    /**
     * @Security("is_granted('ROLE_CHANNELS_RW')")
     * @Rest\Put("/energy-tariffs/{tariffId}/price-lists/{priceListId}")
     */
    public function putEnergyTariffPriceListAction(int $tariffId, int $priceListId, Request $request) {
        $this->ensureApiVersion24($request);
        $priceList = $this->findOwnedPriceListOrThrow($tariffId, $priceListId);
        $data = $this->getRequestData($request);
        if (array_key_exists('name', $data)) {
            $priceList->setName($data['name']);
        }
        if (array_key_exists('billingPeriodStartDay', $data)) {
            $priceList->setBillingPeriodStartDay((int)$data['billingPeriodStartDay']);
        }
        if (array_key_exists('items', $data)) {
            $this->synchronizePriceListItems($priceList, $data['items']);
        }
        $this->getMeasurementLogsEntityManager()->flush();

        return $this->view($this->serializePriceList($priceList));
    }

    /**
     * @Security("is_granted('ROLE_CHANNELS_RW')")
     * @Rest\Delete("/energy-tariffs/{tariffId}/price-lists/{priceListId}")
     */
    public function deleteEnergyTariffPriceListAction(int $tariffId, int $priceListId, Request $request) {
        $this->ensureApiVersion24($request);
        $priceList = $this->findOwnedPriceListOrThrow($tariffId, $priceListId);
        $this->getMeasurementLogsEntityManager()->remove($priceList);
        $this->getMeasurementLogsEntityManager()->flush();
        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_R') and is_granted('accessIdContains', channel)")
     * @Rest\Get("/channels/{channel}/energy-tariff-assignments")
     */
    public function getChannelEnergyTariffAssignmentsAction(IODeviceChannel $channel, Request $request) {
        $this->ensureApiVersion24($request);
        $assignments = $this->getMeasurementLogsEntityManager()->getRepository(EnergyTariffAssignment::class)->findBy(['channelId' => $channel->getId()]);
        return $this->view(array_map(fn(EnergyTariffAssignment $assignment
        ) => $this->serializeTariffAssignment($assignment), $assignments));
    }

    /**
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_RW') and is_granted('accessIdContains', channel)")
     * @Rest\Post("/channels/{channel}/energy-tariff-assignments")
     */
    public function postChannelEnergyTariffAssignmentAction(IODeviceChannel $channel, Request $request) {
        $this->ensureApiVersion24($request);
        $data = $this->getRequestData($request);
        $assignment = new EnergyTariffAssignment();
        $assignment->setChannelId($channel->getId());
        $assignment->setTariff($this->findTariffOrThrow((int)$data['tariffId']));
        $assignment->setValidFrom($this->parseDateTime($data['validFrom']));
        $assignment->setValidTo($this->parseNullableDateTime($data['validTo'] ?? null));
        $this->getMeasurementLogsEntityManager()->persist($assignment);
        $this->getMeasurementLogsEntityManager()->flush();
        return $this->view($this->serializeTariffAssignment($assignment), Response::HTTP_CREATED);
    }

    /**
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_RW') and is_granted('accessIdContains', channel)")
     * @Rest\Put("/channels/{channel}/energy-tariff-assignments/{assignmentId}")
     */
    public function putChannelEnergyTariffAssignmentAction(IODeviceChannel $channel, int $assignmentId, Request $request) {
        $this->ensureApiVersion24($request);
        $assignment = $this->findTariffAssignmentForChannelOrThrow($channel->getId(), $assignmentId);
        $data = $this->getRequestData($request);
        if (array_key_exists('tariffId', $data)) {
            $assignment->setTariff($this->findTariffOrThrow((int)$data['tariffId']));
        }
        if (array_key_exists('validFrom', $data)) {
            $assignment->setValidFrom($this->parseDateTime($data['validFrom']));
        }
        if (array_key_exists('validTo', $data)) {
            $assignment->setValidTo($this->parseNullableDateTime($data['validTo']));
        }
        $this->getMeasurementLogsEntityManager()->flush();
        return $this->view($this->serializeTariffAssignment($assignment));
    }

    /**
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_RW') and is_granted('accessIdContains', channel)")
     * @Rest\Delete("/channels/{channel}/energy-tariff-assignments/{assignmentId}")
     */
    public function deleteChannelEnergyTariffAssignmentAction(IODeviceChannel $channel, int $assignmentId, Request $request) {
        $this->ensureApiVersion24($request);
        $assignment = $this->findTariffAssignmentForChannelOrThrow($channel->getId(), $assignmentId);
        $this->getMeasurementLogsEntityManager()->remove($assignment);
        $this->getMeasurementLogsEntityManager()->flush();
        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_R') and is_granted('accessIdContains', channel)")
     * @Rest\Get("/channels/{channel}/energy-price-list-assignments")
     */
    public function getChannelEnergyPriceListAssignmentsAction(IODeviceChannel $channel, Request $request) {
        $this->ensureApiVersion24($request);
        $assignments = $this->getMeasurementLogsEntityManager()->getRepository(EnergyTariffPriceListAssignment::class)->findBy(['channelId' => $channel->getId()]);
        $assignments = array_values(array_filter($assignments, fn(EnergyTariffPriceListAssignment $assignment
        ) => $assignment->getPriceList()->getUserId() === $this->getCurrentUserOrThrow()->getId()));
        return $this->view(array_map(fn(EnergyTariffPriceListAssignment $assignment
        ) => $this->serializePriceListAssignment($assignment), $assignments));
    }

    /**
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_RW') and is_granted('accessIdContains', channel)")
     * @Rest\Post("/channels/{channel}/energy-price-list-assignments")
     */
    public function postChannelEnergyPriceListAssignmentAction(IODeviceChannel $channel, Request $request) {
        $this->ensureApiVersion24($request);
        $data = $this->getRequestData($request);
        $assignment = new EnergyTariffPriceListAssignment();
        $assignment->setChannelId($channel->getId());
        $assignment->setPriceList($this->findOwnedPriceListByIdOrThrow((int)$data['priceListId']));
        $assignment->setValidFrom($this->parseDateTime($data['validFrom']));
        $assignment->setValidTo($this->parseNullableDateTime($data['validTo'] ?? null));
        $this->getMeasurementLogsEntityManager()->persist($assignment);
        $this->getMeasurementLogsEntityManager()->flush();
        return $this->view($this->serializePriceListAssignment($assignment), Response::HTTP_CREATED);
    }

    /**
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_RW') and is_granted('accessIdContains', channel)")
     * @Rest\Put("/channels/{channel}/energy-price-list-assignments/{assignmentId}")
     */
    public function putChannelEnergyPriceListAssignmentAction(IODeviceChannel $channel, int $assignmentId, Request $request) {
        $this->ensureApiVersion24($request);
        $assignment = $this->findPriceListAssignmentForChannelOrThrow($channel->getId(), $assignmentId);
        Assertion::eq($assignment->getPriceList()->getUserId(), $this->getCurrentUserOrThrow()->getId());
        $data = $this->getRequestData($request);
        if (array_key_exists('priceListId', $data)) {
            $assignment->setPriceList($this->findOwnedPriceListByIdOrThrow((int)$data['priceListId']));
        }
        if (array_key_exists('validFrom', $data)) {
            $assignment->setValidFrom($this->parseDateTime($data['validFrom']));
        }
        if (array_key_exists('validTo', $data)) {
            $assignment->setValidTo($this->parseNullableDateTime($data['validTo']));
        }
        $this->getMeasurementLogsEntityManager()->flush();
        return $this->view($this->serializePriceListAssignment($assignment));
    }

    /**
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_RW') and is_granted('accessIdContains', channel)")
     * @Rest\Delete("/channels/{channel}/energy-price-list-assignments/{assignmentId}")
     */
    public function deleteChannelEnergyPriceListAssignmentAction(IODeviceChannel $channel, int $assignmentId, Request $request) {
        $this->ensureApiVersion24($request);
        $assignment = $this->findPriceListAssignmentForChannelOrThrow($channel->getId(), $assignmentId);
        Assertion::eq($assignment->getPriceList()->getUserId(), $this->getCurrentUserOrThrow()->getId());
        $this->getMeasurementLogsEntityManager()->remove($assignment);
        $this->getMeasurementLogsEntityManager()->flush();
        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_R') and is_granted('accessIdContains', channel)")
     * @Rest\Get("/channels/{channel}/energy-cost-logs")
     */
    public function getChannelEnergyCostLogsAction(IODeviceChannel $channel, Request $request) {
        $this->ensureApiVersion24($request);
        $this->ensureElectricityMeterChannel($channel);
        $rows = $this->fetchElectricityCostRows(
            $channel->getId(),
            (int)$request->query->get('afterTimestamp'),
            (int)$request->query->get('beforeTimestamp'),
            $request->query->get('order') !== 'ASC',
            (int)$request->query->get('limit', self::RECORD_LIMIT_PER_REQUEST),
            (int)$request->query->get('offset', 0)
        );

        return $this->view($this->hydrateCostLogs($rows));
    }

    /**
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_R') and is_granted('accessIdContains', channel)")
     * @Rest\Get("/channels/{channel}/energy-cost-summaries")
     */
    public function getChannelEnergyCostSummariesAction(IODeviceChannel $channel, Request $request) {
        $this->ensureApiVersion24($request);
        $this->ensureElectricityMeterChannel($channel);
        $afterTimestamp = (int)$request->query->get('afterTimestamp');
        $beforeTimestamp = (int)$request->query->get('beforeTimestamp');
        $rows = $this->fetchAllElectricityCostRows($channel->getId(), $afterTimestamp, $beforeTimestamp);

        return $this->view(array_values($this->buildEnergyCostSummaries($channel->getId(), $rows, $afterTimestamp, $beforeTimestamp)));
    }

    private function ensureElectricityMeterChannel(IODeviceChannel $channel): void {
        Assertion::eq($channel->getFunction()->getId(), ChannelFunction::ELECTRICITYMETER, 'Unsupported channel function.');
    }

    private function fetchElectricityCostRows(
        int $channelId,
        int $afterTimestamp,
        int $beforeTimestamp,
        bool $orderDesc,
        int $limit,
        int $offset
    ): array {
        $entityManager = $this->getMeasurementLogsEntityManager();
        $platform = DatabaseUtils::getPlatform($entityManager);
        $order = $orderDesc ? 'DESC' : 'ASC';
        $slotStartExpr = $platform === DatabaseUtils::PSQL
            ? "b.date - INTERVAL '15 minutes'"
            : 'DATE_SUB(b.date, INTERVAL 15 MINUTE)';
        $dateTsExpr = $platform === DatabaseUtils::PSQL ? 'EXTRACT(EPOCH FROM b.date)::INTEGER' : 'UNIX_TIMESTAMP(b.date)';
        $slotStartTsExpr = $platform === DatabaseUtils::PSQL ? "EXTRACT(EPOCH FROM ($slotStartExpr))::INTEGER" : "UNIX_TIMESTAMP($slotStartExpr)";

        $where = 'WHERE d.channel_id = :channelId ';
        if ($afterTimestamp > 0) {
            $where .= 'AND d.date > :afterDate ';
        }
        if ($beforeTimestamp > 0) {
            $where .= 'AND d.date < :beforeDate ';
        }

        $sql = "SELECT $dateTsExpr date_timestamp,
                $slotStartTsExpr slot_start_timestamp,
                b.date,
                b.phase1_fae,
                b.phase2_fae,
                b.phase3_fae,
                ta.tariff_id,
                rz.zone_code,
                pla.price_list_id,
                pli.component_code,
                pli.amount,
                pli.unit,
                pli.currency,
                pl.billing_period_start_day,
                ((COALESCE(b.phase1_fae, 0) + COALESCE(b.phase2_fae, 0) + COALESCE(b.phase3_fae, 0)) / 1000.0) total_kwh,
                (COALESCE(b.phase1_fae, 0) / 1000.0) phase1_kwh,
                (COALESCE(b.phase2_fae, 0) / 1000.0) phase2_kwh,
                (COALESCE(b.phase3_fae, 0) / 1000.0) phase3_kwh
            FROM (
                SELECT d.channel_id, d.date, d.phase1_fae, d.phase2_fae, d.phase3_fae
                FROM supla_em_delta_log d
                $where
                ORDER BY d.date $order
                LIMIT :limit OFFSET :offset
            ) b
            LEFT JOIN supla_energy_tariff_assignment ta
                ON ta.channel_id = b.channel_id
                AND $slotStartExpr >= ta.valid_from
                AND (ta.valid_to IS NULL OR $slotStartExpr < ta.valid_to)
            LEFT JOIN supla_energy_tariff_resolved_zone rz
                ON rz.tariff_id = ta.tariff_id
                AND $slotStartExpr >= rz.period_start
                AND $slotStartExpr < rz.period_end
            LEFT JOIN supla_energy_tariff_price_list_assignment pla
                ON pla.channel_id = b.channel_id
                AND $slotStartExpr >= pla.valid_from
                AND (pla.valid_to IS NULL OR $slotStartExpr < pla.valid_to)
            LEFT JOIN supla_energy_tariff_price_list pl
                ON pl.id = pla.price_list_id
                AND pl.tariff_id = ta.tariff_id
            LEFT JOIN supla_energy_tariff_price_list_item pli
                ON pli.price_list_id = pl.id
                AND pli.unit = 'kWh'
                AND (pli.zone_code = rz.zone_code OR pli.zone_code IS NULL)
            ORDER BY b.date $order, pli.component_code ASC";

        $stmt = $entityManager->getConnection()->prepare($sql);
        $stmt->bindValue('channelId', $channelId, 'integer');
        if ($afterTimestamp > 0) {
            $stmt->bindValue('afterDate', DateUtils::timestampToMysqlUtc($afterTimestamp), 'string');
        }
        if ($beforeTimestamp > 0) {
            $stmt->bindValue('beforeDate', DateUtils::timestampToMysqlUtc($beforeTimestamp), 'string');
        }
        $stmt->bindValue('limit', min(max($limit, 1), self::RECORD_LIMIT_PER_REQUEST), 'integer');
        $stmt->bindValue('offset', max($offset, 0), 'integer');

        return $stmt->executeQuery()->fetchAllAssociative();
    }

    private function fetchAllElectricityCostRows(int $channelId, int $afterTimestamp, int $beforeTimestamp): array {
        $offset = 0;
        $rows = [];
        do {
            $batch = $this->fetchElectricityCostRows(
                $channelId,
                $afterTimestamp,
                $beforeTimestamp,
                false,
                self::RECORD_LIMIT_PER_REQUEST,
                $offset
            );
            $rows = array_merge($rows, $batch);
            $offset += self::RECORD_LIMIT_PER_REQUEST;
        } while (count($batch) === self::RECORD_LIMIT_PER_REQUEST);

        return $rows;
    }

    private function hydrateCostLogs(array $rows): array {
        $logs = [];
        foreach ($rows as $row) {
            $key = (string)$row['date_timestamp'];
            if (!isset($logs[$key])) {
                $phase1 = (int)$row['phase1_fae'];
                $phase2 = (int)$row['phase2_fae'];
                $phase3 = (int)$row['phase3_fae'];
                $logs[$key] = [
                    'dateTimestamp' => (int)$row['date_timestamp'],
                    'slotStartTimestamp' => (int)$row['slot_start_timestamp'],
                    'tariffId' => $row['tariff_id'] !== null ? (int)$row['tariff_id'] : null,
                    'zoneCode' => $row['zone_code'],
                    'priceListId' => $row['price_list_id'] !== null ? (int)$row['price_list_id'] : null,
                    'usage' => [
                        'phase1Fae' => $phase1,
                        'phase2Fae' => $phase2,
                        'phase3Fae' => $phase3,
                        'totalFae' => $phase1 + $phase2 + $phase3,
                        'totalKwh' => round(((float)$row['total_kwh']), 6),
                    ],
                    'costs' => null,
                ];
            }

            if (!$row['component_code']) {
                continue;
            }

            if ($logs[$key]['costs'] === null) {
                $logs[$key]['costs'] = [
                    'currency' => $row['currency'],
                    'total' => 0.0,
                    'byComponent' => [],
                    'byZone' => [],
                    'byPhase' => ['phase1' => 0.0, 'phase2' => 0.0, 'phase3' => 0.0],
                ];
            }

            $componentCost = round((float)$row['total_kwh'] * (float)$row['amount'], 6);
            $phase1Cost = round((float)$row['phase1_kwh'] * (float)$row['amount'], 6);
            $phase2Cost = round((float)$row['phase2_kwh'] * (float)$row['amount'], 6);
            $phase3Cost = round((float)$row['phase3_kwh'] * (float)$row['amount'], 6);

            $logs[$key]['costs']['total'] += $componentCost;
            $logs[$key]['costs']['byComponent'][$row['component_code']] = ($logs[$key]['costs']['byComponent'][$row['component_code']] ?? 0) + $componentCost;
            if ($row['zone_code']) {
                $logs[$key]['costs']['byZone'][$row['zone_code']] = ($logs[$key]['costs']['byZone'][$row['zone_code']] ?? 0) + $componentCost;
            }
            $logs[$key]['costs']['byPhase']['phase1'] += $phase1Cost;
            $logs[$key]['costs']['byPhase']['phase2'] += $phase2Cost;
            $logs[$key]['costs']['byPhase']['phase3'] += $phase3Cost;
        }

        foreach ($logs as &$log) {
            if ($log['costs']) {
                $log['costs']['total'] = round($log['costs']['total'], 6);
                foreach ($log['costs']['byComponent'] as $component => $amount) {
                    $log['costs']['byComponent'][$component] = round($amount, 6);
                }
                foreach ($log['costs']['byZone'] as $zone => $amount) {
                    $log['costs']['byZone'][$zone] = round($amount, 6);
                }
                foreach ($log['costs']['byPhase'] as $phase => $amount) {
                    $log['costs']['byPhase'][$phase] = round($amount, 6);
                }
            }
        }

        return array_values($logs);
    }

    private function buildEnergyCostSummaries(int $channelId, array $rows, int $afterTimestamp, int $beforeTimestamp): array {
        $logs = $this->hydrateCostLogs($rows);
        $tariffIds = array_values(array_unique(array_filter(array_map(fn(array $log) => $log['tariffId'], $logs))));
        $priceListIds = array_values(array_unique(array_filter(array_map(fn(array $log) => $log['priceListId'], $logs))));
        $tariffs = [];
        foreach ($tariffIds as $tariffId) {
            $tariff = $this->getMeasurementLogsEntityManager()->find(EnergyTariff::class, $tariffId);
            if ($tariff) {
                $tariffs[$tariffId] = $tariff;
            }
        }
        $priceLists = [];
        foreach ($priceListIds as $priceListId) {
            $priceList = $this->getMeasurementLogsEntityManager()->find(EnergyTariffPriceList::class, $priceListId);
            if ($priceList) {
                $priceLists[$priceListId] = $priceList;
            }
        }

        $summaries = [];
        foreach ($logs as $log) {
            $context = $this->resolveBillingContext($log, $tariffs, $priceLists);
            $key = $context['key'];
            if (!isset($summaries[$key])) {
                $summaries[$key] = [
                    'periodStart' => $context['periodStart'],
                    'periodEnd' => $context['periodEnd'],
                    'timezone' => $context['timezone'],
                    'usage' => [
                        'totalKwh' => 0.0,
                        'byPhase' => ['phase1' => 0.0, 'phase2' => 0.0, 'phase3' => 0.0],
                    ],
                    'costs' => [
                        'currency' => $log['costs']['currency'] ?? 'PLN',
                        'total' => 0.0,
                        'byComponent' => [],
                        'byZone' => [],
                        'byPhase' => ['phase1' => 0.0, 'phase2' => 0.0, 'phase3' => 0.0],
                    ],
                ];
            }

            $summaries[$key]['usage']['totalKwh'] += $log['usage']['totalKwh'];
            $summaries[$key]['usage']['byPhase']['phase1'] += $log['usage']['phase1Fae'] / 1000;
            $summaries[$key]['usage']['byPhase']['phase2'] += $log['usage']['phase2Fae'] / 1000;
            $summaries[$key]['usage']['byPhase']['phase3'] += $log['usage']['phase3Fae'] / 1000;

            if ($log['costs']) {
                $summaries[$key]['costs']['total'] += $log['costs']['total'];
                foreach ($log['costs']['byComponent'] as $component => $amount) {
                    $summaries[$key]['costs']['byComponent'][$component] = ($summaries[$key]['costs']['byComponent'][$component] ?? 0) + $amount;
                }
                foreach ($log['costs']['byZone'] as $zone => $amount) {
                    $summaries[$key]['costs']['byZone'][$zone] = ($summaries[$key]['costs']['byZone'][$zone] ?? 0) + $amount;
                }
                foreach ($log['costs']['byPhase'] as $phase => $amount) {
                    $summaries[$key]['costs']['byPhase'][$phase] += $amount;
                }
            }
        }

        $this->applyFixedCostsToSummaries($summaries, $channelId, $afterTimestamp, $beforeTimestamp, $tariffs, $priceLists);

        foreach ($summaries as &$summary) {
            $summary['usage']['totalKwh'] = round($summary['usage']['totalKwh'], 6);
            foreach ($summary['usage']['byPhase'] as $phase => $amount) {
                $summary['usage']['byPhase'][$phase] = round($amount, 6);
            }
            $summary['costs']['total'] = round($summary['costs']['total'], 6);
            foreach ($summary['costs']['byComponent'] as $component => $amount) {
                $summary['costs']['byComponent'][$component] = round($amount, 6);
            }
            foreach ($summary['costs']['byZone'] as $zone => $amount) {
                $summary['costs']['byZone'][$zone] = round($amount, 6);
            }
            foreach ($summary['costs']['byPhase'] as $phase => $amount) {
                $summary['costs']['byPhase'][$phase] = round($amount, 6);
            }
        }

        ksort($summaries);
        return $summaries;
    }

    private function applyFixedCostsToSummaries(
        array &$summaries,
        int $channelId,
        int $afterTimestamp,
        int $beforeTimestamp,
        array $tariffs,
        array $priceLists
    ): void {
        $assignments = $this->getMeasurementLogsEntityManager()->getRepository(EnergyTariffPriceListAssignment::class)->findBy(['channelId' => $channelId]);
        $rangeStart = new \DateTime('@' . max($afterTimestamp, 0));
        $rangeStart->setTimezone(new \DateTimeZone('UTC'));
        $rangeEnd = $beforeTimestamp > 0 ? new \DateTime('@' . $beforeTimestamp) : new \DateTime('now', new \DateTimeZone('UTC'));
        $rangeEnd->setTimezone(new \DateTimeZone('UTC'));

        foreach ($assignments as $assignment) {
            $priceList = $assignment->getPriceList();
            if (!$priceList) {
                continue;
            }
            $tariff = $priceList->getTariff();
            $timezone = new \DateTimeZone($tariff?->getConfig()['timezone'] ?? 'UTC');
            $assignmentStart = clone $assignment->getValidFrom();
            $assignmentEnd = $assignment->getValidTo() ? clone $assignment->getValidTo() : clone $rangeEnd;
            $start = $assignmentStart > $rangeStart ? $assignmentStart : clone $rangeStart;
            $end = $assignmentEnd < $rangeEnd ? $assignmentEnd : clone $rangeEnd;
            if ($start >= $end) {
                continue;
            }

            $billingContext = $this->resolveBillingPeriodForTimestamp($start->getTimestamp(), $timezone, $priceList->getBillingPeriodStartDay());
            while (strtotime($billingContext['periodStart']) < $end->getTimestamp()) {
                $summaryKey = $billingContext['periodStart'] . '|' . $billingContext['timezone'];
                if (!isset($summaries[$summaryKey])) {
                    $summaries[$summaryKey] = [
                        'periodStart' => $billingContext['periodStart'],
                        'periodEnd' => $billingContext['periodEnd'],
                        'timezone' => $billingContext['timezone'],
                        'usage' => ['totalKwh' => 0.0, 'byPhase' => ['phase1' => 0.0, 'phase2' => 0.0, 'phase3' => 0.0]],
                        'costs' => ['currency' => 'PLN', 'total' => 0.0, 'byComponent' => [], 'byZone' => [], 'byPhase' => ['phase1' => 0.0, 'phase2' => 0.0, 'phase3' => 0.0]],
                    ];
                }

                foreach ($priceList->getItems() as $item) {
                    if (!in_array($item->getUnit(), ['day', 'month'], true)) {
                        continue;
                    }
                    if ($item->getUnit() === 'month') {
                        $this->addSummaryCost($summaries[$summaryKey], $item->getComponentCode(), (float)$item->getAmount());
                    } else {
                        $days = $this->countOverlappingLocalDays(
                            $billingContext['periodStart'],
                            $billingContext['periodEnd'],
                            $start,
                            $end,
                            $timezone
                        );
                        $this->addSummaryCost($summaries[$summaryKey], $item->getComponentCode(), (float)$item->getAmount() * $days);
                    }
                }

                $next = new \DateTime($billingContext['periodEnd'], new \DateTimeZone('UTC'));
                $billingContext = $this->resolveBillingPeriodForTimestamp($next->getTimestamp(), $timezone, $priceList->getBillingPeriodStartDay());
            }
        }
    }

    private function addSummaryCost(array &$summary, string $componentCode, float $amount): void {
        $summary['costs']['total'] += $amount;
        $summary['costs']['byComponent'][$componentCode] = ($summary['costs']['byComponent'][$componentCode] ?? 0) + $amount;
    }

    private function countOverlappingLocalDays(
        string $periodStart,
        string $periodEnd,
        \DateTime $rangeStart,
        \DateTime $rangeEnd,
        \DateTimeZone $timezone
    ): int {
        $start = new \DateTime($periodStart, new \DateTimeZone('UTC'));
        $end = new \DateTime($periodEnd, new \DateTimeZone('UTC'));
        if ($rangeStart > $start) {
            $start = clone $rangeStart;
        }
        if ($rangeEnd < $end) {
            $end = clone $rangeEnd;
        }
        if ($start >= $end) {
            return 0;
        }
        $start->setTimezone($timezone);
        $end->setTimezone($timezone);
        return max(1, (int)$start->diff($end)->format('%a'));
    }

    private function resolveBillingContext(array $log, array $tariffs, array $priceLists): array {
        $priceList = $log['priceListId'] ? ($priceLists[$log['priceListId']] ?? null) : null;
        $tariff = $log['tariffId'] ? ($tariffs[$log['tariffId']] ?? null) : null;
        $timezone = new \DateTimeZone($tariff?->getConfig()['timezone'] ?? 'UTC');
        $billingStartDay = $priceList?->getBillingPeriodStartDay() ?? 1;
        return $this->resolveBillingPeriodForTimestamp($log['slotStartTimestamp'], $timezone, $billingStartDay);
    }

    private function resolveBillingPeriodForTimestamp(int $timestamp, \DateTimeZone $timezone, int $billingStartDay): array {
        $local = new \DateTime('@' . $timestamp);
        $local->setTimezone($timezone);
        $billingStartDay = max(1, min(28, $billingStartDay));

        $periodStartLocal = clone $local;
        if ((int)$local->format('j') < $billingStartDay) {
            $periodStartLocal->modify('first day of previous month');
        } else {
            $periodStartLocal->modify('first day of this month');
        }
        $periodStartLocal->setDate((int)$periodStartLocal->format('Y'), (int)$periodStartLocal->format('m'), $billingStartDay);
        $periodStartLocal->setTime(0, 0, 0);
        $periodEndLocal = (clone $periodStartLocal)->modify('+1 month');

        $periodStartUtc = clone $periodStartLocal;
        $periodStartUtc->setTimezone(new \DateTimeZone('UTC'));
        $periodEndUtc = clone $periodEndLocal;
        $periodEndUtc->setTimezone(new \DateTimeZone('UTC'));

        return [
            'key' => $periodStartUtc->format(\DateTime::ATOM) . '|' . $timezone->getName(),
            'periodStart' => $periodStartUtc->format(\DateTime::ATOM),
            'periodEnd' => $periodEndUtc->format(\DateTime::ATOM),
            'timezone' => $timezone->getName(),
        ];
    }

    private function ensureApiVersion24(Request $request): void {
        if (!ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($request)) {
            throw new NotFoundHttpException();
        }
    }

    private function getRequestData(Request $request): array {
        return json_decode($request->getContent(), true) ?: [];
    }

    private function findTariffOrThrow(int $id): EnergyTariff {
        $tariff = $this->getMeasurementLogsEntityManager()->find(EnergyTariff::class, $id);
        if (!$tariff) {
            throw new NotFoundHttpException();
        }
        return $tariff;
    }

    private function findOwnedPriceListOrThrow(int $tariffId, int $priceListId): EnergyTariffPriceList {
        $priceList = $this->findOwnedPriceListByIdOrThrow($priceListId);
        if ((int)$priceList->getTariff()?->getId() !== $tariffId) {
            throw new NotFoundHttpException();
        }
        return $priceList;
    }

    private function findOwnedPriceListByIdOrThrow(int $priceListId): EnergyTariffPriceList {
        $priceList = $this->getMeasurementLogsEntityManager()->find(EnergyTariffPriceList::class, $priceListId);
        if (!$priceList || $priceList->getUserId() !== $this->getCurrentUserOrThrow()->getId()) {
            throw new NotFoundHttpException();
        }
        return $priceList;
    }

    private function findTariffAssignmentForChannelOrThrow(int $channelId, int $assignmentId): EnergyTariffAssignment {
        $assignment = $this->getMeasurementLogsEntityManager()->find(EnergyTariffAssignment::class, $assignmentId);
        if (!$assignment || $assignment->getChannelId() !== $channelId) {
            throw new NotFoundHttpException();
        }
        return $assignment;
    }

    private function findPriceListAssignmentForChannelOrThrow(int $channelId, int $assignmentId): EnergyTariffPriceListAssignment {
        $assignment = $this->getMeasurementLogsEntityManager()->find(EnergyTariffPriceListAssignment::class, $assignmentId);
        if (!$assignment || $assignment->getChannelId() !== $channelId) {
            throw new NotFoundHttpException();
        }
        return $assignment;
    }

    private function synchronizePriceListItems(EnergyTariffPriceList $priceList, array $items): void {
        foreach ($priceList->getItems()->toArray() as $item) {
            $priceList->removeItem($item);
            $this->getMeasurementLogsEntityManager()->remove($item);
        }
        foreach ($items as $itemData) {
            $item = new EnergyTariffPriceListItem();
            $item->setComponentCode($itemData['componentCode']);
            $item->setZoneCode($itemData['zoneCode'] ?? null);
            $item->setAmount((float)$itemData['amount']);
            $item->setUnit($itemData['unit']);
            $item->setCurrency($itemData['currency']);
            $priceList->addItem($item);
        }
    }

    private function parseDateTime(string $dateTime): \DateTime {
        return new \DateTime($dateTime, new \DateTimeZone('UTC'));
    }

    private function parseNullableDateTime(?string $dateTime): ?\DateTime {
        return $dateTime ? $this->parseDateTime($dateTime) : null;
    }

    private function getMeasurementLogsEntityManager() {
        return $this->measurementLogsEntityManager;
    }

    private function serializeTariff(EnergyTariff $tariff): array {
        return [
            'id' => $tariff->getId(),
            'code' => $tariff->getCode(),
            'name' => $tariff->getName(),
            'config' => $tariff->getConfig(),
        ];
    }

    private function serializePriceList(EnergyTariffPriceList $priceList): array {
        return [
            'id' => $priceList->getId(),
            'tariffId' => $priceList->getTariff()?->getId(),
            'userId' => $priceList->getUserId(),
            'name' => $priceList->getName(),
            'billingPeriodStartDay' => $priceList->getBillingPeriodStartDay(),
            'items' => array_map(fn(EnergyTariffPriceListItem $item) => [
                'id' => $item->getId(),
                'componentCode' => $item->getComponentCode(),
                'zoneCode' => $item->getZoneCode(),
                'amount' => $item->getAmount(),
                'unit' => $item->getUnit(),
                'currency' => $item->getCurrency(),
            ], $priceList->getItems()->toArray()),
        ];
    }

    private function serializeTariffAssignment(EnergyTariffAssignment $assignment): array {
        return [
            'id' => $assignment->getId(),
            'channelId' => $assignment->getChannelId(),
            'tariffId' => $assignment->getTariff()?->getId(),
            'tariff' => $assignment->getTariff() ? $this->serializeTariff($assignment->getTariff()) : null,
            'validFrom' => $assignment->getValidFrom()->format(\DateTime::ATOM),
            'validTo' => $assignment->getValidTo()?->format(\DateTime::ATOM),
        ];
    }

    private function serializePriceListAssignment(EnergyTariffPriceListAssignment $assignment): array {
        return [
            'id' => $assignment->getId(),
            'channelId' => $assignment->getChannelId(),
            'priceListId' => $assignment->getPriceList()?->getId(),
            'priceList' => $assignment->getPriceList() ? $this->serializePriceList($assignment->getPriceList()) : null,
            'validFrom' => $assignment->getValidFrom()->format(\DateTime::ATOM),
            'validTo' => $assignment->getValidTo()?->format(\DateTime::ATOM),
        ];
    }
}
