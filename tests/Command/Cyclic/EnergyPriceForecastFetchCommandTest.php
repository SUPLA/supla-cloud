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

namespace App\Tests\Command\Cyclic;

use App\Command\Cyclic\EnergyPriceForecastFetchCommand;
use App\Entity\MeasurementLogs\EnergyPriceLogItem;
use App\Supla\SuplaAutodiscover;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class EnergyPriceForecastFetchCommandTest extends TestCase {
    public function testUnregisteredCloudDoesNotFetchForecastOrAccessMeasurementLogs() {
        $ad = $this->createMock(SuplaAutodiscover::class);
        $ad->method('isTarget')->willReturn(false);
        $ad->expects($this->never())->method('fetchEnergyPriceForecast');
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('getRepository');
        $em->expects($this->never())->method('persist');
        $em->expects($this->never())->method('flush');

        $tester = new CommandTester(new EnergyPriceForecastFetchCommand($ad, $em));

        $this->assertSame(0, $tester->execute([]));
        $this->assertSame('', $tester->getDisplay());
    }

    public function testRegisteredCloudFetchesAndStoresForecast() {
        $ad = $this->createMock(SuplaAutodiscover::class);
        $ad->method('isTarget')->willReturn(true);
        $ad->expects($this->once())->method('fetchEnergyPriceForecast')->willReturn([
            ['dateFrom' => '2026-10-21T21:00:00+02:00', 'dateTo' => '2026-10-21T21:14:59+02:00', 'rce' => 441.8],
        ]);
        $repo = $this->createMock(EntityRepository::class);
        $repo->expects($this->once())->method('find')->with('2026-10-21T19:00:00+00:00')->willReturn(null);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('getRepository')->with(EnergyPriceLogItem::class)->willReturn($repo);
        $em->expects($this->once())->method('persist')->with($this->callback(function (EnergyPriceLogItem $log) {
            return $log->getRce() === 441.8;
        }));
        $em->expects($this->once())->method('flush');

        $tester = new CommandTester(new EnergyPriceForecastFetchCommand($ad, $em));

        $this->assertSame(0, $tester->execute([]));
    }
}
