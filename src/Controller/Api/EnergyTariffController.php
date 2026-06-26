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
use App\Entity\MeasurementLogs\EnergyTariffProfile;
use App\Entity\MeasurementLogs\EnergyTariffProfileAssignment;
use App\Entity\MeasurementLogs\EnergyTariffProfilePriceItem;
use App\Entity\MeasurementLogs\EnergyTariffProfilePricePeriod;
use App\Entity\MeasurementLogs\EnergyTariffProfileTariffPeriod;
use App\Enums\BillingPeriodUnit;
use App\Enums\ChannelFunction;
use App\Enums\EnergyPriceComponent;
use App\Enums\EnergyPriceUnit;
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
     * @Rest\Get("/energy-tariff-profiles")
     */
    public function getEnergyTariffProfilesAction(Request $request) {
        $this->ensureApiVersion24($request);
        $profiles = $this->getMeasurementLogsEntityManager()->getRepository(EnergyTariffProfile::class)->findBy([
            'userId' => $this->getCurrentUserOrThrow()->getId(),
        ], ['id' => 'ASC']);
        return $this->view(array_map(fn(EnergyTariffProfile $profile) => $this->serializeProfile($profile), $profiles));
    }

    /**
     * @Security("is_granted('ROLE_CHANNELS_RW')")
     * @Rest\Post("/energy-tariff-profiles")
     */
    public function postEnergyTariffProfileAction(Request $request) {
        $this->ensureApiVersion24($request);
        $data = $this->getRequestData($request);
        Assertion::keyExists($data, 'name');
        Assertion::keyExists($data, 'tariffPeriods');

        $profile = new EnergyTariffProfile();
        $profile->setUserId($this->getCurrentUserOrThrow()->getId());
        $profile->setName($data['name']);
        $this->synchronizeProfileTariffPeriods($profile, $data['tariffPeriods']);
        $this->validateProfile($profile);

        $this->getMeasurementLogsEntityManager()->persist($profile);
        $this->getMeasurementLogsEntityManager()->flush();

        return $this->view($this->serializeProfile($profile), Response::HTTP_CREATED);
    }

    /**
     * @Security("is_granted('ROLE_CHANNELS_R')")
     * @Rest\Get("/energy-tariff-profiles/{profileId}")
     */
    public function getEnergyTariffProfileAction(int $profileId, Request $request) {
        $this->ensureApiVersion24($request);
        return $this->view($this->serializeProfile($this->findOwnedProfileOrThrow($profileId)));
    }

    /**
     * @Security("is_granted('ROLE_CHANNELS_RW')")
     * @Rest\Put("/energy-tariff-profiles/{profileId}")
     */
    public function putEnergyTariffProfileAction(int $profileId, Request $request) {
        $this->ensureApiVersion24($request);
        $profile = $this->findOwnedProfileOrThrow($profileId);
        $data = $this->getRequestData($request);
        if (array_key_exists('name', $data)) {
            $profile->setName($data['name']);
        }
        if (array_key_exists('tariffPeriods', $data)) {
            $this->synchronizeProfileTariffPeriods($profile, $data['tariffPeriods']);
        }
        $this->validateProfile($profile);
        $this->getMeasurementLogsEntityManager()->flush();

        return $this->view($this->serializeProfile($profile));
    }

    /**
     * @Security("is_granted('ROLE_CHANNELS_RW')")
     * @Rest\Delete("/energy-tariff-profiles/{profileId}")
     */
    public function deleteEnergyTariffProfileAction(int $profileId, Request $request) {
        $this->ensureApiVersion24($request);
        $profile = $this->findOwnedProfileOrThrow($profileId);
        $this->getMeasurementLogsEntityManager()->remove($profile);
        $this->getMeasurementLogsEntityManager()->flush();
        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_R') and is_granted('accessIdContains', channel)")
     * @Rest\Get("/channels/{channel}/energy-tariff-profile-assignment")
     */
    public function getChannelEnergyTariffProfileAssignmentAction(IODeviceChannel $channel, Request $request) {
        $this->ensureApiVersion24($request);
        $this->ensureElectricityMeterChannel($channel);
        $assignment = $this->findProfileAssignmentForChannel($channel->getId());
        return $this->view($assignment ? $this->serializeProfileAssignment($assignment) : null);
    }

    /**
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_RW') and is_granted('accessIdContains', channel)")
     * @Rest\Put("/channels/{channel}/energy-tariff-profile-assignment")
     */
    public function putChannelEnergyTariffProfileAssignmentAction(IODeviceChannel $channel, Request $request) {
        $this->ensureApiVersion24($request);
        $this->ensureElectricityMeterChannel($channel);
        $data = $this->getRequestData($request);
        Assertion::keyExists($data, 'profileId');

        $assignment = $this->findProfileAssignmentForChannel($channel->getId()) ?? new EnergyTariffProfileAssignment($channel->getId());
        $assignment->setProfile($this->findOwnedProfileOrThrow((int)$data['profileId']));
        $this->getMeasurementLogsEntityManager()->persist($assignment);
        $this->getMeasurementLogsEntityManager()->flush();

        return $this->view($this->serializeProfileAssignment($assignment));
    }

    /**
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_RW') and is_granted('accessIdContains', channel)")
     * @Rest\Delete("/channels/{channel}/energy-tariff-profile-assignment")
     */
    public function deleteChannelEnergyTariffProfileAssignmentAction(IODeviceChannel $channel, Request $request) {
        $this->ensureApiVersion24($request);
        $this->ensureElectricityMeterChannel($channel);
        $assignment = $this->findProfileAssignmentForChannel($channel->getId());
        if ($assignment) {
            $this->getMeasurementLogsEntityManager()->remove($assignment);
            $this->getMeasurementLogsEntityManager()->flush();
        }
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
                pa.profile_id,
                tp.tariff_id,
                rz.zone_code,
                pp.id price_period_id,
                ppi.component_code,
                ppi.amount,
                ppi.unit,
                pp.currency,
                pp.billing_period_length,
                pp.billing_period_unit,
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
            LEFT JOIN supla_energy_tariff_profile_assignment pa
                ON pa.channel_id = b.channel_id
            LEFT JOIN supla_energy_tariff_profile_tariff_period tp
                ON tp.profile_id = pa.profile_id
                AND (tp.valid_from IS NULL OR $slotStartExpr >= tp.valid_from)
                AND (tp.valid_to IS NULL OR $slotStartExpr < tp.valid_to)
            LEFT JOIN supla_energy_tariff_resolved_zone rz
                ON rz.tariff_id = tp.tariff_id
                AND $slotStartExpr >= rz.period_start
                AND $slotStartExpr < rz.period_end
            LEFT JOIN supla_energy_tariff_profile_price_period pp
                ON pp.tariff_period_id = tp.id
                AND (pp.valid_from IS NULL OR $slotStartExpr >= pp.valid_from)
                AND (pp.valid_to IS NULL OR $slotStartExpr < pp.valid_to)
            LEFT JOIN supla_energy_tariff_profile_price_item ppi
                ON ppi.price_period_id = pp.id
                AND ppi.unit = 'kWh'
                AND (ppi.zone_code = rz.zone_code OR ppi.zone_code IS NULL)
            ORDER BY b.date $order, ppi.component_code ASC";

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
                    'profileId' => $row['profile_id'] !== null ? (int)$row['profile_id'] : null,
                    'tariffId' => $row['tariff_id'] !== null ? (int)$row['tariff_id'] : null,
                    'zoneCode' => $row['zone_code'],
                    'pricePeriodId' => $row['price_period_id'] !== null ? (int)$row['price_period_id'] : null,
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

            $componentCode = EnergyPriceComponent::from((int)$row['component_code'])->name;
            $componentCost = round((float)$row['total_kwh'] * (float)$row['amount'], 6);
            $phase1Cost = round((float)$row['phase1_kwh'] * (float)$row['amount'], 6);
            $phase2Cost = round((float)$row['phase2_kwh'] * (float)$row['amount'], 6);
            $phase3Cost = round((float)$row['phase3_kwh'] * (float)$row['amount'], 6);

            $logs[$key]['costs']['total'] += $componentCost;
            $logs[$key]['costs']['byComponent'][$componentCode] = ($logs[$key]['costs']['byComponent'][$componentCode] ?? 0) + $componentCost;
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
        $pricePeriodIds = array_values(array_unique(array_filter(array_map(fn(array $log) => $log['pricePeriodId'], $logs))));
        $tariffs = [];
        foreach ($tariffIds as $tariffId) {
            $tariff = $this->getMeasurementLogsEntityManager()->find(EnergyTariff::class, $tariffId);
            if ($tariff) {
                $tariffs[$tariffId] = $tariff;
            }
        }
        $pricePeriods = [];
        foreach ($pricePeriodIds as $pricePeriodId) {
            $pricePeriod = $this->getMeasurementLogsEntityManager()->find(EnergyTariffProfilePricePeriod::class, $pricePeriodId);
            if ($pricePeriod) {
                $pricePeriods[$pricePeriodId] = $pricePeriod;
            }
        }

        $summaries = [];
        foreach ($logs as $log) {
            $context = $this->resolveBillingContext($log, $tariffs, $pricePeriods);
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

        $this->applyFixedCostsToSummaries($summaries, $channelId, $afterTimestamp, $beforeTimestamp);

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

    private function applyFixedCostsToSummaries(array &$summaries, int $channelId, int $afterTimestamp, int $beforeTimestamp): void {
        $assignment = $this->findProfileAssignmentForChannel($channelId);
        if (!$assignment || !$assignment->getProfile()) {
            return;
        }

        $rangeStart = new \DateTime('@' . max($afterTimestamp, 0));
        $rangeStart->setTimezone(new \DateTimeZone('UTC'));
        $rangeEnd = $beforeTimestamp > 0 ? new \DateTime('@' . $beforeTimestamp) : new \DateTime('now', new \DateTimeZone('UTC'));
        $rangeEnd->setTimezone(new \DateTimeZone('UTC'));

        foreach ($assignment->getProfile()->getTariffPeriods() as $tariffPeriod) {
            $tariff = $tariffPeriod->getTariff();
            if (!$tariff) {
                continue;
            }
            $timezone = new \DateTimeZone($tariff->getConfig()['timezone'] ?? 'UTC');
            foreach ($tariffPeriod->getPricePeriods() as $pricePeriod) {
                $pricePeriodStart = $this->maxDateTime(
                    clone $rangeStart,
                    $tariffPeriod->getValidFrom(),
                    $pricePeriod->getValidFrom()
                );
                $pricePeriodEnd = $this->minDateTime(
                    clone $rangeEnd,
                    $tariffPeriod->getValidTo(),
                    $pricePeriod->getValidTo()
                );
                $start = $pricePeriodStart > $rangeStart ? $pricePeriodStart : clone $rangeStart;
                $end = $pricePeriodEnd < $rangeEnd ? $pricePeriodEnd : clone $rangeEnd;
                if ($start >= $end) {
                    continue;
                }

                $billingContext = $this->resolveBillingPeriodForTimestamp(
                    $start->getTimestamp(),
                    $timezone,
                    $pricePeriod->getValidFrom(),
                    $pricePeriod->getBillingPeriodLength(),
                    $pricePeriod->getBillingPeriodUnit()
                );
                while (strtotime($billingContext['periodStart']) < $end->getTimestamp()) {
                    $summaryKey = $billingContext['periodStart'] . '|' . $billingContext['timezone'];
                    if (!isset($summaries[$summaryKey])) {
                        $summaries[$summaryKey] = [
                            'periodStart' => $billingContext['periodStart'],
                            'periodEnd' => $billingContext['periodEnd'],
                            'timezone' => $billingContext['timezone'],
                            'usage' => ['totalKwh' => 0.0, 'byPhase' => ['phase1' => 0.0, 'phase2' => 0.0, 'phase3' => 0.0]],
                            'costs' => ['currency' => $pricePeriod->getCurrency(), 'total' => 0.0, 'byComponent' => [], 'byZone' => [], 'byPhase' => ['phase1' => 0.0, 'phase2' => 0.0, 'phase3' => 0.0]],
                        ];
                    }

                    foreach ($pricePeriod->getItems() as $item) {
                        if ($item->getUnit() === EnergyPriceUnit::KWH) {
                            continue;
                        }
                        if ($item->getUnit() === EnergyPriceUnit::PERIOD) {
                            $this->addSummaryCost($summaries[$summaryKey], $item->getComponentCode(), (float)$item->getAmount());
                        } else {
                            $unitCount = $this->countOverlappingFixedUnits(
                                $billingContext['periodStart'],
                                $billingContext['periodEnd'],
                                $start,
                                $end,
                                $timezone,
                                $item->getUnit()
                            );
                            $this->addSummaryCost($summaries[$summaryKey], $item->getComponentCode(), (float)$item->getAmount() * $unitCount);
                        }
                    }

                    $next = new \DateTime($billingContext['periodEnd'], new \DateTimeZone('UTC'));
                    $billingContext = $this->resolveBillingPeriodForTimestamp(
                        $next->getTimestamp(),
                        $timezone,
                        $pricePeriod->getValidFrom(),
                        $pricePeriod->getBillingPeriodLength(),
                        $pricePeriod->getBillingPeriodUnit()
                    );
                }
            }
        }
    }

    private function addSummaryCost(array &$summary, EnergyPriceComponent $componentCode, float $amount): void {
        $summary['costs']['total'] += $amount;
        $summary['costs']['byComponent'][$componentCode->name] = ($summary['costs']['byComponent'][$componentCode->name] ?? 0) + $amount;
    }

    private function countOverlappingFixedUnits(
        string $periodStart,
        string $periodEnd,
        \DateTime $rangeStart,
        \DateTime $rangeEnd,
        \DateTimeZone $timezone,
        EnergyPriceUnit $unit
    ): int {
        $periodStartLocal = new \DateTime($periodStart, new \DateTimeZone('UTC'));
        $periodEndLocal = new \DateTime($periodEnd, new \DateTimeZone('UTC'));
        $periodStartLocal->setTimezone($timezone);
        $periodEndLocal->setTimezone($timezone);
        $overlapStart = clone $periodStartLocal;
        $overlapEnd = clone $periodEndLocal;
        $rangeStartLocal = clone $rangeStart;
        $rangeStartLocal->setTimezone($timezone);
        $rangeEndLocal = clone $rangeEnd;
        $rangeEndLocal->setTimezone($timezone);
        if ($rangeStartLocal > $overlapStart) {
            $overlapStart = clone $rangeStartLocal;
        }
        if ($rangeEndLocal < $overlapEnd) {
            $overlapEnd = clone $rangeEndLocal;
        }
        if ($overlapStart >= $overlapEnd) {
            return 0;
        }
        $cursor = clone $periodStartLocal;
        $count = 0;
        while ($cursor < $periodEndLocal) {
            $next = $this->advanceDateTime(clone $cursor, 1, $this->mapPriceUnitToBillingPeriodUnit($unit));
            if ($next > $periodEndLocal) {
                $next = clone $periodEndLocal;
            }
            if ($next > $overlapStart && $cursor < $overlapEnd) {
                $count++;
            }
            $cursor = $next;
        }
        return $count;
    }

    private function resolveBillingContext(array $log, array $tariffs, array $pricePeriods): array {
        $pricePeriod = $log['pricePeriodId'] ? ($pricePeriods[$log['pricePeriodId']] ?? null) : null;
        $tariff = $log['tariffId'] ? ($tariffs[$log['tariffId']] ?? null) : null;
        $timezone = new \DateTimeZone($tariff?->getConfig()['timezone'] ?? 'UTC');
        if (!$pricePeriod) {
            return $this->resolveDefaultBillingPeriodForTimestamp($log['slotStartTimestamp'], $timezone);
        }
        return $this->resolveBillingPeriodForTimestamp(
            $log['slotStartTimestamp'],
            $timezone,
            $pricePeriod->getValidFrom(),
            $pricePeriod->getBillingPeriodLength(),
            $pricePeriod->getBillingPeriodUnit()
        );
    }

    private function resolveBillingPeriodForTimestamp(
        int $timestamp,
        \DateTimeZone $timezone,
        ?\DateTime $billingAnchorUtc,
        int $billingPeriodLength,
        BillingPeriodUnit $billingPeriodUnit
    ): array {
        if ($billingAnchorUtc === null) {
            return $this->resolveNaturalBillingPeriodForTimestamp($timestamp, $timezone, $billingPeriodLength, $billingPeriodUnit);
        }

        $local = new \DateTime('@' . $timestamp);
        $local->setTimezone($timezone);
        $periodStartLocal = clone $billingAnchorUtc;
        $periodStartLocal->setTimezone($timezone);
        $periodEndLocal = $this->advanceDateTime(clone $periodStartLocal, $billingPeriodLength, $billingPeriodUnit);

        while ($local < $periodStartLocal) {
            $periodEndLocal = clone $periodStartLocal;
            $periodStartLocal = $this->advanceDateTime(clone $periodStartLocal, -$billingPeriodLength, $billingPeriodUnit);
        }

        while ($local >= $periodEndLocal) {
            $periodStartLocal = clone $periodEndLocal;
            $periodEndLocal = $this->advanceDateTime(clone $periodEndLocal, $billingPeriodLength, $billingPeriodUnit);
        }

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

    private function resolveNaturalBillingPeriodForTimestamp(
        int $timestamp,
        \DateTimeZone $timezone,
        int $billingPeriodLength,
        BillingPeriodUnit $billingPeriodUnit
    ): array {
        $local = new \DateTime('@' . $timestamp);
        $local->setTimezone($timezone);
        $periodStartLocal = $this->alignNaturalBillingPeriodStart(clone $local, $billingPeriodLength, $billingPeriodUnit);
        $periodEndLocal = $this->advanceDateTime(clone $periodStartLocal, $billingPeriodLength, $billingPeriodUnit);

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

    private function resolveDefaultBillingPeriodForTimestamp(int $timestamp, \DateTimeZone $timezone): array {
        return $this->resolveNaturalBillingPeriodForTimestamp($timestamp, $timezone, 1, BillingPeriodUnit::MONTH);
    }

    private function alignNaturalBillingPeriodStart(\DateTime $dateTime, int $length, BillingPeriodUnit $unit): \DateTime {
        $length = max($length, 1);

        return match ($unit) {
            BillingPeriodUnit::DAY => $this->alignDayBillingPeriodStart($dateTime, $length),
            BillingPeriodUnit::WEEK => $this->alignWeekBillingPeriodStart($dateTime, $length),
            BillingPeriodUnit::MONTH => $this->alignMonthBillingPeriodStart($dateTime, $length),
            BillingPeriodUnit::YEAR => $this->alignYearBillingPeriodStart($dateTime, $length),
        };
    }

    private function alignDayBillingPeriodStart(\DateTime $dateTime, int $length): \DateTime {
        $dateTime->setTime(0, 0, 0);
        if ($length === 1) {
            return $dateTime;
        }

        $epoch = new \DateTime('1970-01-01 00:00:00', $dateTime->getTimezone());
        $dayOffset = (int)$epoch->diff($dateTime)->format('%r%a');
        $remainder = (($dayOffset % $length) + $length) % $length;
        return $remainder ? $dateTime->modify(sprintf('-%d day', $remainder)) : $dateTime;
    }

    private function alignWeekBillingPeriodStart(\DateTime $dateTime, int $length): \DateTime {
        $dateTime->setTime(0, 0, 0);
        $dateTime->modify('monday this week');
        if ($length === 1) {
            return $dateTime;
        }

        $epoch = new \DateTime('1970-01-05 00:00:00', $dateTime->getTimezone());
        $dayOffset = (int)$epoch->diff($dateTime)->format('%r%a');
        $weekOffset = intdiv($dayOffset, 7);
        $remainder = (($weekOffset % $length) + $length) % $length;
        return $remainder ? $dateTime->modify(sprintf('-%d week', $remainder)) : $dateTime;
    }

    private function alignMonthBillingPeriodStart(\DateTime $dateTime, int $length): \DateTime {
        $dateTime->setDate((int)$dateTime->format('Y'), (int)$dateTime->format('m'), 1);
        $dateTime->setTime(0, 0, 0);
        if ($length === 1) {
            return $dateTime;
        }

        $monthOffset = ((int)$dateTime->format('Y') * 12) + ((int)$dateTime->format('n') - 1);
        $remainder = (($monthOffset % $length) + $length) % $length;
        return $remainder ? $dateTime->modify(sprintf('-%d month', $remainder)) : $dateTime;
    }

    private function alignYearBillingPeriodStart(\DateTime $dateTime, int $length): \DateTime {
        $dateTime->setDate((int)$dateTime->format('Y'), 1, 1);
        $dateTime->setTime(0, 0, 0);
        if ($length === 1) {
            return $dateTime;
        }

        $year = (int)$dateTime->format('Y');
        $remainder = (($year % $length) + $length) % $length;
        return $remainder ? $dateTime->modify(sprintf('-%d year', $remainder)) : $dateTime;
    }

    private function advanceDateTime(\DateTime $dateTime, int $length, BillingPeriodUnit $unit): \DateTime {
        $sign = $length >= 0 ? '+' : '-';
        $absoluteLength = abs($length);
        return match ($unit) {
            BillingPeriodUnit::DAY => $dateTime->modify(sprintf('%s%d day', $sign, $absoluteLength)),
            BillingPeriodUnit::WEEK => $dateTime->modify(sprintf('%s%d week', $sign, $absoluteLength)),
            BillingPeriodUnit::MONTH => $dateTime->modify(sprintf('%s%d month', $sign, $absoluteLength)),
            BillingPeriodUnit::YEAR => $dateTime->modify(sprintf('%s%d year', $sign, $absoluteLength)),
        };
    }

    private function mapPriceUnitToBillingPeriodUnit(EnergyPriceUnit $unit): BillingPeriodUnit {
        return match ($unit) {
            EnergyPriceUnit::DAY => BillingPeriodUnit::DAY,
            EnergyPriceUnit::WEEK => BillingPeriodUnit::WEEK,
            EnergyPriceUnit::MONTH => BillingPeriodUnit::MONTH,
            default => throw new \InvalidArgumentException('Unsupported fixed price unit.'),
        };
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

    private function findOwnedProfileOrThrow(int $profileId): EnergyTariffProfile {
        $profile = $this->getMeasurementLogsEntityManager()->find(EnergyTariffProfile::class, $profileId);
        if (!$profile || $profile->getUserId() !== $this->getCurrentUserOrThrow()->getId()) {
            throw new NotFoundHttpException();
        }
        return $profile;
    }

    private function findProfileAssignmentForChannel(int $channelId): ?EnergyTariffProfileAssignment {
        return $this->getMeasurementLogsEntityManager()->getRepository(EnergyTariffProfileAssignment::class)->findOneBy([
            'channelId' => $channelId,
        ]);
    }

    private function synchronizeProfileTariffPeriods(EnergyTariffProfile $profile, array $tariffPeriods): void {
        Assertion::isArray($tariffPeriods);
        foreach ($profile->getTariffPeriods()->toArray() as $tariffPeriod) {
            $profile->removeTariffPeriod($tariffPeriod);
            $this->getMeasurementLogsEntityManager()->remove($tariffPeriod);
        }

        foreach ($tariffPeriods as $tariffPeriodData) {
            Assertion::keyExists($tariffPeriodData, 'tariffId');
            Assertion::keyExists($tariffPeriodData, 'pricePeriods');

            $tariffPeriod = new EnergyTariffProfileTariffPeriod();
            $tariffPeriod->setTariff($this->findTariffOrThrow((int)$tariffPeriodData['tariffId']));
            $tariffPeriod->setValidFrom($this->parseNullableDateTime($tariffPeriodData['validFrom'] ?? null));
            $tariffPeriod->setValidTo($this->parseNullableDateTime($tariffPeriodData['validTo'] ?? null));
            $this->synchronizePricePeriods($tariffPeriod, $tariffPeriodData['pricePeriods']);
            $profile->addTariffPeriod($tariffPeriod);
        }
    }

    private function synchronizePricePeriods(EnergyTariffProfileTariffPeriod $tariffPeriod, array $pricePeriods): void {
        Assertion::isArray($pricePeriods);
        foreach ($tariffPeriod->getPricePeriods()->toArray() as $pricePeriod) {
            $tariffPeriod->removePricePeriod($pricePeriod);
            $this->getMeasurementLogsEntityManager()->remove($pricePeriod);
        }

        foreach ($pricePeriods as $pricePeriodData) {
            Assertion::keyExists($pricePeriodData, 'billingPeriodLength');
            Assertion::keyExists($pricePeriodData, 'billingPeriodUnit');
            Assertion::keyExists($pricePeriodData, 'currency');
            Assertion::keyExists($pricePeriodData, 'items');

            $pricePeriod = new EnergyTariffProfilePricePeriod();
            $pricePeriod->setBillingPeriodLength((int)$pricePeriodData['billingPeriodLength']);
            $pricePeriod->setBillingPeriodUnit($this->parseBillingPeriodUnit($pricePeriodData['billingPeriodUnit']));
            $pricePeriod->setCurrency($pricePeriodData['currency']);
            $pricePeriod->setValidFrom($this->parseNullableDateTime($pricePeriodData['validFrom'] ?? null));
            $pricePeriod->setValidTo($this->parseNullableDateTime($pricePeriodData['validTo'] ?? null));
            $this->synchronizePriceItems($pricePeriod, $pricePeriodData['items']);
            $tariffPeriod->addPricePeriod($pricePeriod);
        }
    }

    private function synchronizePriceItems(EnergyTariffProfilePricePeriod $pricePeriod, array $items): void {
        Assertion::isArray($items);
        foreach ($pricePeriod->getItems()->toArray() as $item) {
            $pricePeriod->removeItem($item);
            $this->getMeasurementLogsEntityManager()->remove($item);
        }

        foreach ($items as $itemData) {
            Assertion::keyExists($itemData, 'componentCode');
            Assertion::keyExists($itemData, 'amount');
            Assertion::keyExists($itemData, 'unit');
            $item = new EnergyTariffProfilePriceItem();
            $item->setComponentCode($this->parseEnergyPriceComponent($itemData['componentCode']));
            $item->setZoneCode($itemData['zoneCode'] ?? null);
            $item->setAmount((float)$itemData['amount']);
            $item->setUnit($this->parseEnergyPriceUnit($itemData['unit']));
            $pricePeriod->addItem($item);
        }
    }

    private function validateProfile(EnergyTariffProfile $profile): void {
        Assertion::notBlank($profile->getName());
        $tariffPeriods = $profile->getTariffPeriods()->toArray();
        Assertion::notEmpty($tariffPeriods);

        usort($tariffPeriods, fn(
            EnergyTariffProfileTariffPeriod $left,
            EnergyTariffProfileTariffPeriod $right
        ) => $this->compareDateTimeStartNullable($left->getValidFrom(), $right->getValidFrom()));
        $previousTariffPeriod = null;
        foreach ($tariffPeriods as $tariffPeriod) {
            $this->assertEndAfterStart($tariffPeriod->getValidFrom(), $tariffPeriod->getValidTo());
            if ($previousTariffPeriod) {
                Assertion::true(
                    $this->compareEndToStart($previousTariffPeriod->getValidTo(), $tariffPeriod->getValidFrom()) <= 0,
                    'Tariff periods cannot overlap.'
                );
            }
            $this->validatePricePeriodsCoverage($tariffPeriod);
            $previousTariffPeriod = $tariffPeriod;
        }
    }

    private function validatePricePeriodsCoverage(EnergyTariffProfileTariffPeriod $tariffPeriod): void {
        $pricePeriods = $tariffPeriod->getPricePeriods()->toArray();
        Assertion::notEmpty($pricePeriods, 'Tariff period must contain at least one price period.');
        usort($pricePeriods, fn(
            EnergyTariffProfilePricePeriod $left,
            EnergyTariffProfilePricePeriod $right
        ) => $this->compareDateTimeStartNullable($left->getValidFrom(), $right->getValidFrom()));

        $zoneCodes = array_map(fn(array $zone) => $zone['code'], $tariffPeriod->getTariff()?->getConfig()['zones'] ?? []);
        $previousPricePeriod = null;
        foreach ($pricePeriods as $index => $pricePeriod) {
            Assertion::greaterThan($pricePeriod->getBillingPeriodLength(), 0);
            Assertion::regex($pricePeriod->getCurrency(), '/^[A-Z]{3}$/');
            $this->assertEndAfterStart($pricePeriod->getValidFrom(), $pricePeriod->getValidTo());
            if ($tariffPeriod->getValidFrom() && $pricePeriod->getValidFrom()) {
                Assertion::greaterOrEqualThan($pricePeriod->getValidFrom()->getTimestamp(), $tariffPeriod->getValidFrom()->getTimestamp(), 'Price period cannot start before the tariff period.');
            }
            if ($tariffPeriod->getValidTo()) {
                Assertion::notNull($pricePeriod->getValidTo(), 'Price periods must not exceed the tariff period.');
                Assertion::lessOrEqualThan($pricePeriod->getValidTo()->getTimestamp(), $tariffPeriod->getValidTo()->getTimestamp(), 'Price period cannot end after the tariff period.');
            }
            if ($index === 0) {
                Assertion::eq(
                    $this->compareDateTimeStartNullable($pricePeriod->getValidFrom(), $tariffPeriod->getValidFrom()),
                    0,
                    'Price periods must cover the full tariff period.'
                );
            }
            if ($previousPricePeriod) {
                Assertion::eq(
                    $this->compareEndToStart($previousPricePeriod->getValidTo(), $pricePeriod->getValidFrom()),
                    0,
                    'Price periods cannot overlap and must cover the full tariff period without gaps.'
                );
            }
            $items = $pricePeriod->getItems()->toArray();
            Assertion::notEmpty($items, 'Price period must contain at least one price item.');
            foreach ($items as $item) {
                Assertion::true($item->getComponentCode()->supportsUnit($item->getUnit()), 'Price item unit is not allowed for selected component.');
                if ($item->getZoneCode() !== null && $zoneCodes) {
                    Assertion::inArray($item->getZoneCode(), $zoneCodes, 'Price item zone must exist in selected tariff.');
                }
            }
            $previousPricePeriod = $pricePeriod;
        }

        Assertion::eq(
            $this->compareDateTimeNullable(end($pricePeriods)->getValidTo(), $tariffPeriod->getValidTo()),
            0,
            'Price periods must cover the full tariff period.'
        );
    }

    private function assertEndAfterStart(?\DateTime $validFrom, ?\DateTime $validTo): void {
        if ($validFrom && $validTo) {
            Assertion::greaterThan($validTo->getTimestamp(), $validFrom->getTimestamp(), 'Period end must be later than period start.');
        }
    }

    private function compareDateTimeStartNullable(?\DateTime $left, ?\DateTime $right): int {
        if ($left === null && $right === null) {
            return 0;
        }
        if ($left === null) {
            return -1;
        }
        if ($right === null) {
            return 1;
        }
        return $left <=> $right;
    }

    private function compareEndToStart(?\DateTime $left, ?\DateTime $right): int {
        if ($left === null && $right === null) {
            return 1;
        }
        if ($left === null) {
            return 1;
        }
        if ($right === null) {
            return 1;
        }
        return $left <=> $right;
    }

    private function compareDateTimeNullable(?\DateTime $left, ?\DateTime $right): int {
        if ($left === null && $right === null) {
            return 0;
        }
        if ($left === null) {
            return 1;
        }
        if ($right === null) {
            return -1;
        }
        return $left <=> $right;
    }

    private function maxDateTime(\DateTime $base, ?\DateTime ...$candidates): \DateTime {
        $max = clone $base;
        foreach ($candidates as $candidate) {
            if ($candidate && $candidate > $max) {
                $max = clone $candidate;
            }
        }
        return $max;
    }

    private function minDateTime(\DateTime $base, ?\DateTime ...$candidates): \DateTime {
        $min = clone $base;
        foreach ($candidates as $candidate) {
            if ($candidate && $candidate < $min) {
                $min = clone $candidate;
            }
        }
        return $min;
    }

    private function parseDateTime(string $dateTime): \DateTime {
        return new \DateTime($dateTime, new \DateTimeZone('UTC'));
    }

    private function parseNullableDateTime(?string $dateTime): ?\DateTime {
        return $dateTime ? $this->parseDateTime($dateTime) : null;
    }

    /**
     * Accept enum case names for API stability and integer values for internal compatibility.
     */
    private function parseEnergyPriceComponent(string|int $componentCode): EnergyPriceComponent {
        if (is_int($componentCode) || ctype_digit((string)$componentCode)) {
            return EnergyPriceComponent::from((int)$componentCode);
        }

        $constantName = EnergyPriceComponent::class . '::' . $componentCode;
        $component = defined($constantName) ? constant($constantName) : null;
        Assertion::notNull($component, 'Invalid energy price component.');
        return $component;
    }

    private function parseEnergyPriceUnit(string $unit): EnergyPriceUnit {
        $parsed = EnergyPriceUnit::tryFrom($unit);
        Assertion::notNull($parsed, 'Invalid energy price unit.');
        return $parsed;
    }

    private function parseBillingPeriodUnit(string $unit): BillingPeriodUnit {
        $parsed = BillingPeriodUnit::tryFrom($unit);
        Assertion::notNull($parsed, 'Invalid billing period unit.');
        return $parsed;
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

    private function serializeProfile(EnergyTariffProfile $profile): array {
        return [
            'id' => $profile->getId(),
            'userId' => $profile->getUserId(),
            'name' => $profile->getName(),
            'tariffPeriods' => array_map(fn(EnergyTariffProfileTariffPeriod $tariffPeriod
            ) => $this->serializeTariffPeriod($tariffPeriod), $profile->getTariffPeriods()->toArray()),
        ];
    }

    private function serializeTariffPeriod(EnergyTariffProfileTariffPeriod $tariffPeriod): array {
        return [
            'id' => $tariffPeriod->getId(),
            'tariffId' => $tariffPeriod->getTariff()?->getId(),
            'tariff' => $tariffPeriod->getTariff() ? $this->serializeTariff($tariffPeriod->getTariff()) : null,
            'validFrom' => $tariffPeriod->getValidFrom()?->format(\DateTime::ATOM),
            'validTo' => $tariffPeriod->getValidTo()?->format(\DateTime::ATOM),
            'pricePeriods' => array_map(fn(EnergyTariffProfilePricePeriod $pricePeriod
            ) => $this->serializePricePeriod($pricePeriod), $tariffPeriod->getPricePeriods()->toArray()),
        ];
    }

    private function serializePricePeriod(EnergyTariffProfilePricePeriod $pricePeriod): array {
        return [
            'id' => $pricePeriod->getId(),
            'billingPeriodLength' => $pricePeriod->getBillingPeriodLength(),
            'billingPeriodUnit' => $pricePeriod->getBillingPeriodUnit()->value,
            'currency' => $pricePeriod->getCurrency(),
            'validFrom' => $pricePeriod->getValidFrom()?->format(\DateTime::ATOM),
            'validTo' => $pricePeriod->getValidTo()?->format(\DateTime::ATOM),
            'items' => array_map(fn(EnergyTariffProfilePriceItem $item) => [
                'id' => $item->getId(),
                'componentCode' => $item->getComponentCode()->name,
                'zoneCode' => $item->getZoneCode(),
                'amount' => $item->getAmount(),
                'unit' => $item->getUnit()->value,
            ], $pricePeriod->getItems()->toArray()),
        ];
    }

    private function serializeProfileAssignment(EnergyTariffProfileAssignment $assignment): array {
        return [
            'channelId' => $assignment->getChannelId(),
            'profileId' => $assignment->getProfile()?->getId(),
            'profile' => $assignment->getProfile() ? $this->serializeProfile($assignment->getProfile()) : null,
        ];
    }
}
