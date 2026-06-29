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

namespace App\DataFixtures;

use App\Entity\Main\IODevice;
use App\Entity\Main\IODeviceChannel;
use App\Entity\MeasurementLogs\EnergyTariff;
use App\Entity\MeasurementLogs\EnergyTariffProfile;
use App\Entity\MeasurementLogs\EnergyTariffProfileAssignment;
use App\Entity\MeasurementLogs\EnergyTariffProfilePriceItem;
use App\Entity\MeasurementLogs\EnergyTariffProfilePricePeriod;
use App\Entity\MeasurementLogs\EnergyTariffProfileTariffPeriod;
use App\Enums\BillingPeriodUnit;
use App\Enums\ChannelType;
use App\Enums\EnergyPriceComponent;
use App\Enums\EnergyPriceUnit;
use App\Model\MeasurementLogs\TariffDefinitionImporter;
use App\Model\MeasurementLogsEntityManagerProvider;
use Doctrine\Persistence\ObjectManager;

class TariffsFixture extends SuplaFixture {

    public const ORDER = DevicesFixture::ORDER + 1;

    public function __construct(
        private readonly MeasurementLogsEntityManagerProvider $measurementLogsEntityManagerProvider,
        private readonly TariffDefinitionImporter $tariffDefinitionImporter,
    ) {
    }

    public function load(ObjectManager $manager): void {
        $logsEm = $this->measurementLogsEntityManagerProvider->get();
        $definitions = $this->tariffDefinitionImporter->loadDefinitionsFromFile(
            __DIR__ . '/tariff-definitions.json'
        );
        $tariffs = $this->tariffDefinitionImporter->importDefinitions($definitions)['tariffsByCode'];
        $this->createSampleG11Profile($logsEm, $tariffs['PL_G11']);
        $this->createSampleG12Profile($logsEm, $tariffs['PL_G12']);
        $logsEm->flush();
    }

    private function createSampleG11Profile($logsEm, EnergyTariff $tariff): void {
        $device = $this->getReference(DevicesFixture::DEVICE_EVERY_FUNCTION, IODevice::class);
        $channel = $device->getChannels()->filter(fn(IODeviceChannel $channel
        ) => $channel->getType()->getId() === ChannelType::ELECTRICITYMETER)->first();
        if (!$channel) {
            return;
        }

        $profile = new EnergyTariffProfile();
        $profile->setUserId($channel->getUser()->getId());
        $profile->setName('Sample G11 profile');

        $tariffPeriod = new EnergyTariffProfileTariffPeriod();
        $tariffPeriod->setTariff($tariff);
        $profile->addTariffPeriod($tariffPeriod);

        $pricePeriod = new EnergyTariffProfilePricePeriod();
        $pricePeriod->setBillingPeriodLength(1);
        $pricePeriod->setBillingPeriodUnit(BillingPeriodUnit::MONTH);
        $pricePeriod->setCurrency('PLN');
        $pricePeriod->addItem(
            $this->createProfilePriceItem(EnergyPriceComponent::FORWARD_ACTIVE_ENERGY, 'ALL_DAY', 0.95, EnergyPriceUnit::KWH)
        );
        $pricePeriod->addItem(
            $this->createProfilePriceItem(EnergyPriceComponent::DISTRIBUTION_VARIABLE, 'ALL_DAY', 0.11, EnergyPriceUnit::KWH)
        );
        $pricePeriod->addItem(
            $this->createProfilePriceItem(EnergyPriceComponent::DISTRIBUTION_FIXED, null, 12.12, EnergyPriceUnit::MONTH)
        );
        $tariffPeriod->addPricePeriod($pricePeriod);

        $profileAssignment = new EnergyTariffProfileAssignment($channel->getId());
        $profileAssignment->setProfile($profile);

        $logsEm->persist($profile);
        $logsEm->persist($profileAssignment);
    }

    private function createSampleG12Profile($logsEm, EnergyTariff $tariff): void {
        $device = $this->getReference(DevicesFixture::DEVICE_EVERY_FUNCTION, IODevice::class);

        $profile = new EnergyTariffProfile();
        $profile->setUserId($device->getUser()->getId());
        $profile->setName('Sample G12 profile');

        $tariffPeriod = new EnergyTariffProfileTariffPeriod();
        $tariffPeriod->setTariff($tariff);
        $tariffPeriod->setValidFrom(new \DateTime('2025-01-01 00:00:00', new \DateTimeZone('UTC')));
        $profile->addTariffPeriod($tariffPeriod);

        $pricePeriod = new EnergyTariffProfilePricePeriod();
        $pricePeriod->setBillingPeriodLength(2);
        $pricePeriod->setBillingPeriodUnit(BillingPeriodUnit::MONTH);
        $pricePeriod->setCurrency('PLN');
        $pricePeriod->setValidFrom(
            new \DateTime('2025-01-01 00:00:00', new \DateTimeZone('UTC'))
        );
        $pricePeriod->addItem(
            $this->createProfilePriceItem(EnergyPriceComponent::FORWARD_ACTIVE_ENERGY, 'NIGHT', 0.75, EnergyPriceUnit::KWH)
        );
        $pricePeriod->addItem(
            $this->createProfilePriceItem(EnergyPriceComponent::FORWARD_ACTIVE_ENERGY, 'DAY', 0.95, EnergyPriceUnit::KWH)
        );
        $pricePeriod->addItem(
            $this->createProfilePriceItem(EnergyPriceComponent::DISTRIBUTION_VARIABLE, 'NIGHT', 0.11, EnergyPriceUnit::KWH)
        );
        $pricePeriod->addItem(
            $this->createProfilePriceItem(EnergyPriceComponent::DISTRIBUTION_VARIABLE, 'DAY', 0.12, EnergyPriceUnit::KWH)
        );
        $pricePeriod->addItem(
            $this->createProfilePriceItem(EnergyPriceComponent::DISTRIBUTION_FIXED, null, 12.12, EnergyPriceUnit::MONTH)
        );
        $pricePeriod->addItem(
            $this->createProfilePriceItem(EnergyPriceComponent::FEE_FIXED, null, 10, EnergyPriceUnit::PERIOD)
        );
        $tariffPeriod->addPricePeriod($pricePeriod);

        $logsEm->persist($profile);
    }

    private function createProfilePriceItem(
        EnergyPriceComponent $componentCode,
        ?string $zoneCode,
        float $amount,
        EnergyPriceUnit $unit
    ): EnergyTariffProfilePriceItem {
        $item = new EnergyTariffProfilePriceItem();
        $item->setComponentCode($componentCode);
        $item->setZoneCode($zoneCode);
        $item->setAmount($amount);
        $item->setUnit($unit);
        return $item;
    }
}
