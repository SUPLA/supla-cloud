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
use App\Entity\MeasurementLogs\EnergyTariffAssignment;
use App\Entity\MeasurementLogs\EnergyTariffPriceList;
use App\Entity\MeasurementLogs\EnergyTariffPriceListAssignment;
use App\Entity\MeasurementLogs\EnergyTariffPriceListItem;
use App\Enums\ChannelType;
use App\Model\MeasurementLogsEntityManagerProvider;
use Doctrine\Persistence\ObjectManager;

class TariffsFixture extends SuplaFixture {
    public const ORDER = DevicesFixture::ORDER + 1;

    public function __construct(private readonly MeasurementLogsEntityManagerProvider $measurementLogsEntityManagerProvider) {
    }

    public function load(ObjectManager $manager): void {
        $logsEm = $this->measurementLogsEntityManagerProvider->get();
        $tariffs = [];
        foreach ($this->getTariffDefinitions() as $definition) {
            $tariff = new EnergyTariff();
            $tariff->setCode($definition['code']);
            $tariff->setName($definition['name']);
            $tariff->setConfig($definition['config']);
            $logsEm->persist($tariff);
            $tariffs[$definition['code']] = $tariff;
        }
        $logsEm->flush();
        $this->createSampleAssignment($logsEm, $tariffs['PL_G11']);
        $logsEm->flush();
    }

    private function createSampleAssignment($logsEm, EnergyTariff $tariff): void {
        $device = $this->getReference(DevicesFixture::DEVICE_EVERY_FUNCTION, IODevice::class);
        $channel = $device->getChannels()->filter(fn(IODeviceChannel $channel
        ) => $channel->getType()->getId() === ChannelType::ELECTRICITYMETER)->first();
        if (!$channel) {
            return;
        }

        $tariffAssignment = new EnergyTariffAssignment();
        $tariffAssignment->setChannelId($channel->getId());
        $tariffAssignment->setTariff($tariff);
        $tariffAssignment->setValidFrom(new \DateTime('2025-01-01 00:00:00', new \DateTimeZone('UTC')));
        $logsEm->persist($tariffAssignment);

        $priceList = new EnergyTariffPriceList();
        $priceList->setTariff($tariff);
        $priceList->setUserId($channel->getUser()->getId());
        $priceList->setName('Sample G11 price list');
        $priceList->setBillingPeriodStartDay(1);
        $priceList->addItem($this->createPriceListItem('ENERGY_ACTIVE_IMPORT', 'ALL_DAY', 0.95, 'kWh'));
        $priceList->addItem($this->createPriceListItem('DISTRIBUTION_VARIABLE', 'ALL_DAY', 0.11, 'kWh'));
        $priceList->addItem($this->createPriceListItem('DISTRIBUTION_FIXED', null, 12.12, 'month'));
        $logsEm->persist($priceList);

        $priceListAssignment = new EnergyTariffPriceListAssignment();
        $priceListAssignment->setChannelId($channel->getId());
        $priceListAssignment->setPriceList($priceList);
        $priceListAssignment->setValidFrom(new \DateTime('2025-01-01 00:00:00', new \DateTimeZone('UTC')));
        $logsEm->persist($priceListAssignment);
    }

    private function createPriceListItem(string $componentCode, ?string $zoneCode, float $amount, string $unit): EnergyTariffPriceListItem {
        $item = new EnergyTariffPriceListItem();
        $item->setComponentCode($componentCode);
        $item->setZoneCode($zoneCode);
        $item->setAmount($amount);
        $item->setUnit($unit);
        $item->setCurrency('PLN');
        return $item;
    }

    private function getTariffDefinitions(): array {
        return [
            [
                'code' => 'PL_G11',
                'name' => 'G11 - jedna strefa',
                'config' => [
                    'id' => 'PL_G11',
                    'timezone' => 'Europe/Warsaw',
                    'zones' => [['code' => 'ALL_DAY']],
                    'rules' => [[
                        'zone' => 'ALL_DAY',
                        'days' => ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'],
                        'time_ranges' => [['from' => '00:00', 'to' => '00:00']],
                    ]],
                ],
            ],
            [
                'code' => 'PL_G12',
                'name' => 'G12 - dzień / noc',
                'config' => [
                    'id' => 'PL_G12',
                    'timezone' => 'Europe/Warsaw',
                    'zones' => [['code' => 'DAY'], ['code' => 'NIGHT']],
                    'rules' => [
                        [
                            'zone' => 'NIGHT',
                            'days' => ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'],
                            'time_ranges' => [['from' => '22:00', 'to' => '06:00']],
                        ],
                        [
                            'zone' => 'DAY',
                            'days' => ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'],
                            'time_ranges' => [['from' => '06:00', 'to' => '22:00']],
                        ],
                    ],
                ],
            ],
            [
                'code' => 'PL_G13_TAURON',
                'name' => 'G13 - trzy strefy, sezon lato/zima',
                'config' => [
                    'id' => 'PL_G13_TAURON',
                    'name' => 'G13 - trzy strefy, sezon lato/zima',
                    'timezone' => 'Europe/Warsaw',
                    'seasons' => [
                        ['id' => 'SUMMER', 'name' => 'lato', 'from' => '--04-01', 'to' => '--10-01'],
                        ['id' => 'WINTER', 'name' => 'zima', 'from' => '--10-01', 'to' => '--04-01'],
                    ],
                    'zones' => [
                        ['code' => 'MORNING_PEAK', 'name' => 'szczyt przedpołudniowy'],
                        ['code' => 'AFTERNOON_PEAK', 'name' => 'szczyt popołudniowy'],
                        ['code' => 'OFF_PEAK', 'name' => 'pozostałe godziny'],
                    ],
                    'rules' => [
                        [
                            'id' => 'off-peak-weekends-holidays',
                            'priority' => 100,
                            'zone' => 'OFF_PEAK',
                            'days' => ['sat', 'sun', 'holiday'],
                            'time_ranges' => [['from' => '00:00', 'to' => '24:00']],
                        ],
                        [
                            'id' => 'morning-peak-all-year',
                            'priority' => 200,
                            'zone' => 'MORNING_PEAK',
                            'season' => '*',
                            'days' => ['mon', 'tue', 'wed', 'thu', 'fri'],
                            'time_ranges' => [['from' => '07:00', 'to' => '13:00']],
                        ],
                        [
                            'id' => 'afternoon-peak-summer',
                            'priority' => 200,
                            'zone' => 'AFTERNOON_PEAK',
                            'season' => 'SUMMER',
                            'days' => ['mon', 'tue', 'wed', 'thu', 'fri'],
                            'time_ranges' => [['from' => '19:00', 'to' => '22:00']],
                        ],
                        [
                            'id' => 'afternoon-peak-winter',
                            'priority' => 200,
                            'zone' => 'AFTERNOON_PEAK',
                            'season' => 'WINTER',
                            'days' => ['mon', 'tue', 'wed', 'thu', 'fri'],
                            'time_ranges' => [['from' => '16:00', 'to' => '21:00']],
                        ],
                        [
                            'id' => 'default-off-peak',
                            'priority' => 999,
                            'zone' => 'OFF_PEAK',
                            'season' => '*',
                            'days' => ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun', 'holiday'],
                            'time_ranges' => [['from' => '00:00', 'to' => '24:00']],
                        ],
                    ],
                ],
            ],
        ];
    }
}
