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

namespace App\Tests\Integration\Command;

use App\Entity\MeasurementLogs\EnergyTariff;
use App\Model\MeasurementLogsEntityManagerProvider;
use App\Tests\Integration\IntegrationTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/** @small */
class LoadTariffsCommandIntegrationTest extends IntegrationTestCase {

    public function testLoadingSingleTariffDefinition(): void {
        $command = $this->application->find('supla:load-tariffs');
        $commandTester = new CommandTester($command);
        $filePath = $this->createTempTariffFile([
            'code' => 'TEST_SINGLE',
            'name' => 'Test single tariff',
            'config' => [
                'id' => 'TEST_SINGLE',
                'timezone' => 'Europe/Warsaw',
                'zones' => [['code' => 'ALL_DAY']],
                'rules' => [],
            ],
        ]);

        try {
            $exitCode = $commandTester->execute(['file' => $filePath]);
            $this->assertSame(Command::SUCCESS, $exitCode);

            $tariff = $this->getLogsEntityManager()
                ->getRepository(EnergyTariff::class)
                ->findOneBy(['code' => 'TEST_SINGLE']);
            $this->assertNotNull($tariff);
            $this->assertSame('Test single tariff', $tariff->getName());
            $this->assertSame('Europe/Warsaw', $tariff->getConfig()['timezone']);
        } finally {
            @unlink($filePath);
        }
    }

    public function testLoadingTariffArrayUpdatesExistingTariffs(): void {
        $existingTariff = new EnergyTariff();
        $existingTariff->setCode('TEST_EXISTING');
        $existingTariff->setName('Old tariff name');
        $existingTariff->setConfig(['timezone' => 'UTC']);
        $this->getLogsEntityManager()->persist($existingTariff);
        $this->getLogsEntityManager()->flush();

        $command = $this->application->find('supla:load-tariffs');
        $commandTester = new CommandTester($command);
        $filePath = $this->createTempTariffFile([
            [
                'code' => 'TEST_EXISTING',
                'name' => 'Updated tariff name',
                'config' => [
                    'id' => 'TEST_EXISTING',
                    'timezone' => 'Europe/Warsaw',
                    'zones' => [['code' => 'DAY']],
                    'rules' => [],
                ],
            ],
            [
                'code' => 'TEST_NEW',
                'name' => 'New tariff name',
                'config' => [
                    'id' => 'TEST_NEW',
                    'timezone' => 'UTC',
                    'zones' => [['code' => 'NIGHT']],
                    'rules' => [],
                ],
            ],
        ]);

        try {
            $exitCode = $commandTester->execute(['file' => $filePath]);
            $this->assertSame(Command::SUCCESS, $exitCode);
            $this->assertStringContainsString('Created: 1, updated: 1.', $commandTester->getDisplay());

            $this->getLogsEntityManager()->clear();
            $repository = $this->getLogsEntityManager()->getRepository(EnergyTariff::class);
            /** @var EnergyTariff $updatedTariff */
            $updatedTariff = $repository->findOneBy(['code' => 'TEST_EXISTING']);
            $this->assertSame('Updated tariff name', $updatedTariff->getName());
            $this->assertSame('Europe/Warsaw', $updatedTariff->getConfig()['timezone']);
            $this->assertNotNull($repository->findOneBy(['code' => 'TEST_NEW']));
        } finally {
            @unlink($filePath);
        }
    }

    private function getLogsEntityManager(): EntityManagerInterface {
        return self::getContainer()->get(MeasurementLogsEntityManagerProvider::class)->get();
    }

    private function createTempTariffFile(array $definition): string {
        $filePath = tempnam(sys_get_temp_dir(), 'tariff-definition-');
        $this->assertNotFalse($filePath);
        file_put_contents($filePath, json_encode($definition, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
        return $filePath;
    }
}
