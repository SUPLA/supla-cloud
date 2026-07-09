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

namespace App\Command\Cyclic;

use App\Model\MeasurementLogs\EnergyTariffDynamicPriceMaterializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MaterializeDynamicEnergyTariffPricesCommand extends AbstractCyclicCommand {
    public function __construct(
        private readonly EnergyTariffDynamicPriceMaterializer $dynamicPriceMaterializer,
        private readonly EntityManagerInterface $measurementLogsEntityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this
            ->setHidden(true)
            ->setName('supla:cyclic:materialize-dynamic-energy-tariff-prices')
            ->setDescription('Materializes 15-minute dynamic energy tariff prices from energy price logs.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $this->dynamicPriceMaterializer->materializeAll();
        $this->measurementLogsEntityManager->flush();
        return self::SUCCESS;
    }

    protected function getIntervalInMinutes(): int {
        return 60;
    }
}
