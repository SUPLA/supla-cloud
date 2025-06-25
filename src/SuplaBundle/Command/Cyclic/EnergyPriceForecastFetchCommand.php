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

namespace SuplaBundle\Command\Cyclic;

use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\MeasurementLogs\EnergyPriceLogItem;
use SuplaBundle\Model\TimeProvider;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Supla\SuplaAutodiscover;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EnergyPriceForecastFetchCommand extends Command implements CyclicCommand {
    use Transactional;

    public function __construct(private SuplaAutodiscover $ad, private EntityManagerInterface $measurementLogsEntityManager) {
        parent::__construct();
    }

    protected function configure() {
        $this
            ->setHidden(true)
            ->setName('supla:cyclic:energy-price-forecast-fetch')
            ->setDescription('Fetches data for energy forecast channels.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $repo = $this->measurementLogsEntityManager->getRepository(EnergyPriceLogItem::class);
        $forecast = $this->ad->fetchEnergyPriceForecast();
        foreach ($forecast as $forecastItem) {
            $dateFrom = new \DateTime($forecastItem['dateFrom']);
            $dateFrom->setTimezone(new \DateTimeZone('UTC'));
            if (!($log = $repo->find($dateFrom->format(\DateTime::ATOM)))) {
                $log = new EnergyPriceLogItem($dateFrom, new \DateTime($forecastItem['dateTo']));
            }
            if (($forecastItem['rce'] ?? null) !== null) {
                $log->setRce($forecastItem['rce']);
            }
            if (($forecastItem['pdgsz'] ?? null) !== null) {
                $log->setPdgsz($forecastItem['pdgsz']);
            }
            if (($forecastItem['fixing1'] ?? null) !== null) {
                $log->setFixing1($forecastItem['fixing1']);
            }
            if (($forecastItem['fixing2'] ?? null) !== null) {
                $log->setFixing2($forecastItem['fixing2']);
            }
            $this->measurementLogsEntityManager->persist($log);
        }
        $this->measurementLogsEntityManager->flush();
        return 0;
    }

    public function shouldRunNow(TimeProvider $timeProvider): bool {
        $timestamp = $timeProvider->getTimestamp();
        $minuteInTheDay = intval(date('H', $timestamp)) * 60 + intval(date('i', $timestamp));
        return in_array($minuteInTheDay, [
            2 * 60 + 10,
            4 * 60 + 10,
            8 * 60 + 10,
            11 * 60 + 10,
            13 * 60 + 10,
            16 * 60 + 10,
            17 * 60 + 10,
            18 * 60 + 10,
            19 * 60 + 10,
            20 * 60 + 10,
            22 * 60 + 10,
        ]);
    }
}
