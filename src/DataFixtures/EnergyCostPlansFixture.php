<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 */

namespace App\DataFixtures;

use App\Entity\Main\EnergyCostPlan;
use App\Entity\Main\EnergyCostPlanAssignment;
use App\Entity\Main\IODeviceChannel;
use App\Entity\Main\User;
use DateTime;
use Doctrine\Persistence\ObjectManager;
use Supla\EnergyCostCalculator\Plan\CostPlanStarterCatalog;

class EnergyCostPlansFixture extends SuplaFixture {
    public const ORDER = DevicesFixture::ORDER + 1;

    public function load(ObjectManager $manager): void {
        $user = $this->getReference(UsersFixture::USER, User::class);
        $createdAt = new DateTime();
        $g11Plan = new EnergyCostPlan($user, 'Home G11', $this->g11Configuration(), $createdAt);
        $g12Plan = new EnergyCostPlan($user, 'Home G12', $this->g12Configuration(), $createdAt);

        $manager->persist($g11Plan);
        $manager->persist($g12Plan);
        $manager->persist(new EnergyCostPlanAssignment(
            $this->getReference(DevicesFixture::CHANNEL_ELECTRICITY_METER, IODeviceChannel::class),
            $g12Plan,
        ));
        $manager->flush();
    }

    /** @return array<string, mixed> */
    private function g11Configuration(): array {
        return [
            'version' => 2,
            'currency' => 'PLN',
            'timezone' => 'Europe/Warsaw',
            'billingCycles' => [[
                'validFrom' => '2026-01-01T00:00:00+01:00',
                'validTo' => '2027-01-01T00:00:00+01:00',
                'anchor' => '2026-01-01',
                'length' => 1,
                'unit' => 'MONTH',
            ]],
            'periods' => [[
                'validFrom' => '2026-01-01T00:00:00+01:00',
                'validTo' => '2027-01-01T00:00:00+01:00',
                'components' => $this->starterComponents('PL.STARTER.TAURON_DYSTRYBUCJA.G11'),
            ]],
        ];
    }

    /** @return array<string, mixed> */
    private function g12Configuration(): array {
        return [
            'version' => 2,
            'currency' => 'PLN',
            'timezone' => 'Europe/Warsaw',
            'billingCycles' => [[
                'validFrom' => '2026-01-01T00:00:00+01:00',
                'validTo' => '2027-01-01T00:00:00+01:00',
                'anchor' => '2026-01-01',
                'length' => 1,
                'unit' => 'MONTH',
            ]],
            'periods' => [[
                'validFrom' => '2026-01-01T00:00:00+01:00',
                'validTo' => '2027-01-01T00:00:00+01:00',
                'components' => $this->starterComponents('PL.STARTER.TAURON_DYSTRYBUCJA.G12'),
            ]],
        ];
    }

    /** @return list<array<string, mixed>> */
    private function starterComponents(string $starterId): array {
        return (new CostPlanStarterCatalog())->get($starterId)->components;
    }
}
