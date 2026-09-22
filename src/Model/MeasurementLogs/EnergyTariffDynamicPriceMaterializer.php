<?php

namespace App\Model\MeasurementLogs;

use App\Entity\MeasurementLogs\EnergyPriceLogItem;
use App\Entity\MeasurementLogs\EnergyTariff;
use App\Entity\MeasurementLogs\EnergyTariffDynamicPrice;
use App\Enums\EnergyPriceComponent;
use App\Enums\EnergyTariffDynamicPriceSource;
use Doctrine\ORM\EntityManagerInterface;

readonly class EnergyTariffDynamicPriceMaterializer {
    public function __construct(
        private EntityManagerInterface $measurementLogsEntityManager,
    ) {
    }

    public function materializeAll(): void {
        $tariffs = $this->measurementLogsEntityManager->getRepository(EnergyTariff::class)->findAll();
        $dynamicTariffs = array_values(array_filter($tariffs, fn(EnergyTariff $tariff) => $tariff->isDynamic()));
        $dynamicTariffIds = array_map(fn(EnergyTariff $tariff) => (int)$tariff->getId(), $dynamicTariffs);

        $deleteBuilder = $this->measurementLogsEntityManager->createQueryBuilder()
            ->delete(EnergyTariffDynamicPrice::class, 'price');
        if ($dynamicTariffIds) {
            $deleteBuilder
                ->where('IDENTITY(price.tariff) NOT IN (:tariffIds)')
                ->setParameter('tariffIds', $dynamicTariffIds);
        }
        $deleteBuilder->getQuery()->execute();

        foreach ($dynamicTariffs as $tariff) {
            $this->materializeTariff($tariff);
        }
    }

    public function materializeTariff(EnergyTariff $tariff): void {
        if (!$tariff->isDynamic()) {
            $this->measurementLogsEntityManager->createQueryBuilder()
                ->delete(EnergyTariffDynamicPrice::class, 'price')
                ->where('IDENTITY(price.tariff) = :tariffId')
                ->setParameter('tariffId', $tariff->getId())
                ->getQuery()
                ->execute();
            return;
        }

        $sourceConfig = $tariff->getDynamicPriceSourceConfig();
        $source = EnergyTariffDynamicPriceSource::from($sourceConfig['source']);
        $currency = (string)$sourceConfig['currency'];
        $multiplier = (float)($sourceConfig['multiplier'] ?? 1);
        $component = EnergyPriceComponent::FORWARD_ACTIVE_ENERGY;

        $existingRows = $this->measurementLogsEntityManager->getRepository(EnergyTariffDynamicPrice::class)->findBy([
            'tariff' => $tariff,
            'componentCode' => $component,
        ]);
        $existingBySlotStart = [];
        foreach ($existingRows as $row) {
            $existingBySlotStart[$row->getDateFrom()->format('Y-m-d H:i:s')] = $row;
        }

        $keptKeys = [];
        foreach ($this->measurementLogsEntityManager->getRepository(EnergyPriceLogItem::class)->findAll() as $log) {
            $sourceValue = $source->extractValue($log);
            $slotKey = $log->getDateFrom()->format('Y-m-d H:i:s');
            if ($sourceValue === null) {
                if (isset($existingBySlotStart[$slotKey])) {
                    $this->measurementLogsEntityManager->remove($existingBySlotStart[$slotKey]);
                }
                continue;
            }

            $price = $existingBySlotStart[$slotKey] ?? new EnergyTariffDynamicPrice();
            $price->setTariff($tariff);
            $price->setComponentCode($component);
            $price->setDateFrom(clone $log->getDateFrom());
            $price->setDateTo(clone $log->getDateTo());
            $price->setCurrency($currency);
            $price->setAmount(round($sourceValue * $multiplier, 6));
            $this->measurementLogsEntityManager->persist($price);
            $keptKeys[$slotKey] = true;
        }

        foreach ($existingBySlotStart as $slotKey => $existingRow) {
            if (!isset($keptKeys[$slotKey])) {
                $this->measurementLogsEntityManager->remove($existingRow);
            }
        }
    }
}
