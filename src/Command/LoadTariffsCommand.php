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

namespace App\Command;

use App\Model\MeasurementLogs\TariffDefinitionImporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

class LoadTariffsCommand extends Command {
    public function __construct(
        private readonly TariffDefinitionImporter $tariffDefinitionImporter,
        KernelInterface $kernel,
    ) {
        $this->projectDir = $kernel->getProjectDir();
        parent::__construct();
    }

    private readonly string $projectDir;

    protected function configure(): void {
        $this
            ->setName('supla:load-tariffs')
            ->setDescription('Loads energy tariffs from a JSON definition file.')
            ->addArgument(
                'file',
                InputArgument::OPTIONAL,
                'Path to a JSON file containing a tariff object or an array of tariff objects.',
                'src/DataFixtures/tariff-definitions.json'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);
        $filePath = $this->resolveFilePath((string)$input->getArgument('file'));

        $definitions = $this->tariffDefinitionImporter->loadDefinitionsFromFile($filePath);
        $result = $this->tariffDefinitionImporter->importDefinitions($definitions);

        $io->success(sprintf(
            'Imported %d tariff(s) from %s. Created: %d, updated: %d.',
            count($result['tariffsByCode']),
            $filePath,
            $result['created'],
            $result['updated']
        ));

        if ($output->isVerbose()) {
            $io->listing(array_keys($result['tariffsByCode']));
        }

        return self::SUCCESS;
    }

    private function resolveFilePath(string $filePath): string {
        if ($filePath === '') {
            return $this->projectDir . '/src/DataFixtures/tariff-definitions.json';
        }

        if (str_starts_with($filePath, '/')) {
            return $filePath;
        }

        return $this->projectDir . '/' . $filePath;
    }
}
