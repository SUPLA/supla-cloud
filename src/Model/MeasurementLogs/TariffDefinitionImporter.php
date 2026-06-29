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

namespace App\Model\MeasurementLogs;

use App\Entity\MeasurementLogs\EnergyTariff;
use App\Model\MeasurementLogsEntityManagerProvider;

class TariffDefinitionImporter {

    public function __construct(
        private readonly MeasurementLogsEntityManagerProvider $measurementLogsEntityManagerProvider,
    ) {
    }

    public function loadDefinitionsFromFile(string $filePath): array {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new \InvalidArgumentException(sprintf(
                'Tariff definition file "%s" does not exist or is not readable.',
                $filePath
            ));
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \RuntimeException(sprintf('Could not read tariff definition file "%s".', $filePath));
        }

        $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        return $this->normalizeDefinitions($decoded);
    }

    public function importDefinitions(array $definitions): array {
        $logsEm = $this->measurementLogsEntityManagerProvider->get();
        $repository = $logsEm->getRepository(EnergyTariff::class);
        $result = ['created' => 0, 'updated' => 0, 'tariffsByCode' => []];

        foreach ($definitions as $index => $definition) {
            if (!is_array($definition)) {
                throw new \InvalidArgumentException(sprintf(
                    'Tariff definition at index %d must be an object.',
                    $index
                ));
            }
            $this->assertValidDefinition($definition, $index);

            /** @var EnergyTariff|null $tariff */
            $tariff = $repository->findOneBy(['code' => $definition['code']]);
            if ($tariff) {
                ++$result['updated'];
            } else {
                $tariff = new EnergyTariff();
                ++$result['created'];
            }

            $tariff->setCode($definition['code']);
            $tariff->setName($definition['name']);
            $tariff->setConfig($definition['config']);
            $logsEm->persist($tariff);
            $result['tariffsByCode'][$definition['code']] = $tariff;
        }

        $logsEm->flush();

        return $result;
    }

    private function normalizeDefinitions(mixed $decoded): array {
        if (!is_array($decoded)) {
            throw new \InvalidArgumentException(
                'Tariff definitions JSON must decode to an object or an array of objects.'
            );
        }

        if (array_is_list($decoded)) {
            return $decoded;
        }

        return [$decoded];
    }

    private function assertValidDefinition(array $definition, int $index): void {
        foreach (['code', 'name', 'config'] as $requiredKey) {
            if (!array_key_exists($requiredKey, $definition)) {
                throw new \InvalidArgumentException(sprintf(
                    'Tariff definition at index %d is missing the "%s" field.',
                    $index,
                    $requiredKey
                ));
            }
        }

        if (!is_string($definition['code']) || $definition['code'] === '') {
            throw new \InvalidArgumentException(sprintf(
                'Tariff definition at index %d must contain a non-empty string "code".',
                $index
            ));
        }

        if (!is_string($definition['name']) || $definition['name'] === '') {
            throw new \InvalidArgumentException(sprintf(
                'Tariff definition at index %d must contain a non-empty string "name".',
                $index
            ));
        }

        if (!is_array($definition['config'])) {
            throw new \InvalidArgumentException(sprintf(
                'Tariff definition at index %d must contain an object "config".',
                $index
            ));
        }
    }
}
