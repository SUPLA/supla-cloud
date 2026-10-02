<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 */

namespace App\Controller\Api;

use App\Entity\Main\IODeviceChannel;
use App\Entity\MeasurementLogs\EnergyPriceLogItem;
use App\Enums\VirtualChannelType;
use App\Model\ApiVersions;
use App\Model\TimeProvider;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChannelEnergyPriceLogsController extends RestController {
    private const PRICE_FIELDS = [
        'rce' => 'rce',
        'pdgsz' => 'pdgsz',
        'fixing1' => 'fixing1',
        'fixing2' => 'fixing2',
        'fixing1_hourly' => 'fixing1Hourly',
        'fixing2_hourly' => 'fixing2Hourly',
    ];

    public function __construct(
        private readonly EntityManagerInterface $measurementLogsEntityManager,
        private readonly TimeProvider $timeProvider,
    ) {
    }

    /**
     * @OA\Get(
     *     path="/channels/{channel}/energy-price-logs", operationId="getChannelEnergyPriceLogs",
     *     summary="Get recent and forecast energy prices for a virtual channel.", tags={"Channels"},
     *     @OA\Parameter(description="ID", in="path", name="channel", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(type="array", @OA\Items(
     *         @OA\Property(property="dateTimestamp", type="integer"),
     *         @OA\Property(property="value", type="number", format="float", nullable=true),
     *     ))),
     * )
     * @Rest\Get("/channels/{channel}/energy-price-logs")
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_R') and is_granted('accessIdContains', channel)")
     */
    public function getEnergyPriceLogsAction(Request $request, IODeviceChannel $channel) {
        if (!ApiVersions::V3()->isRequestedEqualOrGreaterThan($request)) {
            throw new NotFoundHttpException();
        }
        $field = $this->getPriceField($channel);
        $repository = $this->measurementLogsEntityManager->getRepository(EnergyPriceLogItem::class);
        $latestLog = $repository->createQueryBuilder('log')
            ->where('log.' . $field . ' IS NOT NULL')
            ->orderBy('log.dateFrom', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        if (!$latestLog) {
            return $this->view([]);
        }

        $now = $this->timeProvider->getDateTime();
        $end = min($latestLog->getDateFrom(), (clone $now)->add(new \DateInterval('P7D')));
        if ($end < $now) {
            $end = clone $now;
        }
        $start = (clone $end)->sub(new \DateInterval('P7D'));
        $logs = $repository->createQueryBuilder('log')
            ->where('log.dateFrom >= :start')
            ->andWhere('log.dateFrom <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('log.dateFrom', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->view(array_map(fn(EnergyPriceLogItem $log) => [
            'dateTimestamp' => $log->getDateFrom()->getTimestamp(),
            'value' => $log->{'get' . ucfirst($field)}(),
        ], $logs));
    }

    private function getPriceField(IODeviceChannel $channel): string {
        $config = $channel->getProperty('virtualChannelConfig', []);
        if (!$channel->isVirtual() || ($config['type'] ?? null) !== VirtualChannelType::ENERGY_PRICE_FORECAST) {
            throw new NotFoundHttpException();
        }
        $field = $config['energyField'] ?? null;
        if (!isset(self::PRICE_FIELDS[$field])) {
            throw new NotFoundHttpException();
        }
        return self::PRICE_FIELDS[$field];
    }
}
