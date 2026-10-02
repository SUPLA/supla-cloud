<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 */

namespace App\DataFixtures;

use App\Entity\EntityUtils;
use App\Entity\Main\IODevice;
use App\Entity\Main\IODeviceChannel;
use App\Entity\MeasurementLogs\ElectricityMeterLogItem;
use App\Entity\MeasurementLogs\EnergyPriceLogItem;
use App\Enums\ChannelType;
use App\Model\MeasurementLogsEntityManagerProvider;
use App\Tests\Integration\Traits\MysqlUtcDate;
use App\Utils\ElectricityMeterValueConverter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class ElectricityMeterLogItemsFixture extends SuplaFixture {
    const ORDER = LogItemsFixture::ORDER + 1;
    private const INTERVAL_SECONDS = 600;
    private const INITIAL_ENERGY_KWH = 1000;
    private const SINCE = '-300 day';
    private const PRICE_FORECAST_DAYS = 2;

    private readonly EntityManagerInterface $entityManager;

    public function __construct(MeasurementLogsEntityManagerProvider $entityManager) {
        $this->entityManager = $entityManager->get();
    }

    public function load(ObjectManager $manager): void {
        $device = $this->getReference(DevicesFixture::DEVICE_EVERY_FUNCTION, IODevice::class);
        $ecChannel = $device->getChannels()->filter(function (IODeviceChannel $channel) {
            return $channel->getType()->getId() === ChannelType::ELECTRICITYMETER;
        })->first();
        $from = strtotime(self::SINCE);
        $to = time();
        $this->create($ecChannel->getId(), $from, $to);
        $this->createEnergyPriceLogItems($from, $to);
        $this->entityManager->flush();
    }

    public function create(int $channelId, int $from, int $to): void {
        $state = $this->initialState();
        $day = null;
        $dayFactor = 1;
        $sunny = false;
        for ($timestamp = $from; $timestamp < $to; $timestamp += self::INTERVAL_SECONDS) {
            $date = (new \DateTimeImmutable('@' . $timestamp))->setTimezone(new \DateTimeZone(date_default_timezone_get()));
            $dayKey = $date->format('Y-m-d');
            if ($dayKey !== $day) {
                $day = $dayKey;
                $dayFactor = random_int(85, 115) / 100;
                $sunny = random_int(1, 100) <= 55;
            }

            [$consumption, $production] = $this->energyForInterval($date, $dayFactor, $sunny);
            $this->addEnergy($state, $consumption, $production);

            $logItem = new ElectricityMeterLogItem();
            EntityUtils::setField($logItem, 'channel_id', $channelId);
            EntityUtils::setField($logItem, 'date', MysqlUtcDate::toString('@' . $timestamp));
            foreach ($state as $name => $value) {
                EntityUtils::setField($logItem, $name, $value);
            }
            if (random_int(1, 100) <= 95) {
                $this->entityManager->persist($logItem);
            }
        }
    }

    private function initialState(): array {
        $initialValue = self::INITIAL_ENERGY_KWH * ElectricityMeterValueConverter::RAW_ENERGY_PRECISION;
        return [
            'phase1_fae' => $initialValue,
            'phase1_rae' => 0,
            'phase1_fre' => (int)($initialValue * .15),
            'phase1_rre' => (int)($initialValue * .02),
            'phase2_fae' => $initialValue,
            'phase2_rae' => 0,
            'phase2_fre' => (int)($initialValue * .15),
            'phase2_rre' => (int)($initialValue * .02),
            'phase3_fae' => $initialValue,
            'phase3_rae' => 0,
            'phase3_fre' => (int)($initialValue * .15),
            'phase3_rre' => (int)($initialValue * .02),
            'fae_balanced' => $initialValue * 3,
            'rae_balanced' => 0,
        ];
    }

    private function energyForInterval(\DateTimeImmutable $date, float $dayFactor, bool $sunny): array {
        $hour = (int)$date->format('G') + (int)$date->format('i') / 60;
        $dayOfWeek = (int)$date->format('N');
        $consumptionWatts = 120;
        $consumptionWatts += $this->bellCurve($hour, 7.5, 1.4) * 500;
        $consumptionWatts += $this->bellCurve($hour, 19, 2.3) * 950;
        if ($dayOfWeek === 6) {
            $consumptionWatts += 120 + $this->bellCurve($hour, 13, 3.5) * 200;
        } elseif ($dayOfWeek === 7) {
            $consumptionWatts += 170 + $this->bellCurve($hour, 13, 4) * 260;
        }
        $consumptionWatts *= $dayFactor * (random_int(92, 108) / 100);

        $daylightHours = 8 + 8 * sin((2 * M_PI * ((int)$date->format('z') - 80)) / 365);
        $sunrise = 12 - $daylightHours / 2;
        $sunset = 12 + $daylightHours / 2;
        $productionWatts = 0;
        if ($hour >= $sunrise && $hour <= $sunset) {
            $solarShape = sin(M_PI * ($hour - $sunrise) / $daylightHours);
            $weatherFactor = $sunny ? random_int(80, 110) / 100 : random_int(3, 25) / 100;
            $productionWatts = 4800 * $solarShape * $weatherFactor;
        }

        $rawEnergyPerWatt = ElectricityMeterValueConverter::RAW_ENERGY_PRECISION / 6000;
        return [(int)round($consumptionWatts * $rawEnergyPerWatt), (int)round($productionWatts * $rawEnergyPerWatt)];
    }

    private function addEnergy(array &$state, int $consumption, int $production): void {
        $phaseShares = [.38, .31, .31];
        $totalImport = 0;
        $totalExport = 0;
        foreach ($phaseShares as $index => $share) {
            $phase = $index + 1;
            $import = (int)round($consumption * $share);
            $export = (int)round($production * $share);
            $state['phase' . $phase . '_fae'] += $import;
            $state['phase' . $phase . '_rae'] += $export;
            $state['phase' . $phase . '_fre'] += (int)round($import * .15);
            $state['phase' . $phase . '_rre'] += (int)round($export * .02);
            $totalImport += $import;
            $totalExport += $export;
        }
        $state['fae_balanced'] += $totalImport;
        $state['rae_balanced'] += $totalExport;
    }

    private function bellCurve(float $value, float $center, float $width): float {
        return exp(-0.5 * (($value - $center) / $width) ** 2);
    }

    private function createEnergyPriceLogItems(int $from, int $to): void {
        $timezone = new \DateTimeZone('Europe/Warsaw');
        $firstDay = (new \DateTimeImmutable('@' . $from))->setTimezone($timezone)->setTime(0, 0);
        $lastDay = (new \DateTimeImmutable('@' . $to))
            ->setTimezone($timezone)
            ->modify('tomorrow midnight')
            ->modify('+' . self::PRICE_FORECAST_DAYS . ' days');

        for ($day = $firstDay; $day < $lastDay; $day = $day->modify('+1 day')) {
            $dayFactor = random_int(85, 115) / 100;
            $sunny = random_int(1, 100) <= 55;
            $hourlyValues = [];
            $slots = [];
            for ($timestamp = $day->getTimestamp(); $timestamp < $day->modify('+1 day')->getTimestamp(); $timestamp += 900) {
                $dateFrom = (new \DateTimeImmutable('@' . $timestamp))->setTimezone($timezone);
                $hourKey = $dateFrom->format('Y-m-d H O');
                $marketPrice = $this->marketPrice($dateFrom, $dayFactor, $sunny);
                if (!isset($hourlyValues[$hourKey])) {
                    $hourlyValues[$hourKey] = [
                        'fixing1Total' => 0,
                        'fixing2Total' => 0,
                        'slots' => 0,
                        'pdgsz' => $this->pdgszForHour($dateFrom, $marketPrice, $sunny),
                    ];
                }
                $fixing1 = $this->priceWithNoise($marketPrice, 45);
                $fixing2 = $this->priceWithNoise($marketPrice, 40);
                $hourlyValues[$hourKey]['fixing1Total'] += $fixing1;
                $hourlyValues[$hourKey]['fixing2Total'] += $fixing2;
                $hourlyValues[$hourKey]['slots']++;
                $slots[] = compact('dateFrom', 'hourKey', 'marketPrice', 'fixing1', 'fixing2');
            }

            foreach ($slots as $slot) {
                $logItem = new EnergyPriceLogItem(
                    new \DateTime($slot['dateFrom']->format(\DateTime::ATOM)),
                    new \DateTime($slot['dateFrom']->modify('+14 minutes 59 seconds')->format(\DateTime::ATOM))
                );
                $hourly = $hourlyValues[$slot['hourKey']];
                $logItem->setRce($this->priceWithNoise($slot['marketPrice'], 75));
                $logItem->setPdgsz($hourly['pdgsz']);
                $logItem->setFixing1($slot['fixing1']);
                $logItem->setFixing2($slot['fixing2']);
                $logItem->setFixing1Hourly(round($hourly['fixing1Total'] / $hourly['slots'], 2));
                $logItem->setFixing2Hourly(round($hourly['fixing2Total'] / $hourly['slots'], 2));
                $this->entityManager->persist($logItem);
            }
        }
    }

    private function marketPrice(\DateTimeImmutable $date, float $dayFactor, bool $sunny): float {
        $hour = (int)$date->format('G') + (int)$date->format('i') / 60;
        $dayOfWeek = (int)$date->format('N');
        $dayOfYear = (int)$date->format('z');
        $winterFactor = (1 + cos((2 * M_PI * ($dayOfYear - 15)) / 365)) / 2;
        $price = 275 + $winterFactor * 140;
        $price -= $this->bellCurve($hour, 2.5, 2.5) * 130;
        $price += $this->bellCurve($hour, 8, 1.8) * 120;
        $price += $this->bellCurve($hour, 18.5, 2.5) * 230;
        if ($dayOfWeek >= 6) {
            $price -= 55;
        }
        if ($sunny) {
            $price -= (1 - $winterFactor) * $this->bellCurve($hour, 13, 2.4) * 270;
        }
        if ($dayOfWeek <= 5 && $hour >= 17 && $hour <= 20 && random_int(1, 100) <= 3) {
            $price += random_int(250, 650);
        }
        return $price * $dayFactor;
    }

    private function pdgszForHour(\DateTimeImmutable $date, float $marketPrice, bool $sunny): int {
        $hour = (int)$date->format('G');
        $dayOfWeek = (int)$date->format('N');
        if ($hour < 6 || ($sunny && $hour >= 11 && $hour <= 15 && $marketPrice < 250)) {
            return 0;
        }
        if ($dayOfWeek <= 5 && $hour >= 17 && $hour <= 20 && $marketPrice > 650 && random_int(1, 100) <= 20) {
            return 2;
        }
        return random_int(1, 100) <= 15 ? 0 : 1;
    }

    private function priceWithNoise(float $price, int $noise): float {
        return round(max(-250, min(2500, $price + random_int(-$noise, $noise))), 2);
    }
}
