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
            'version' => 1,
            'entries' => [[
                'presetId' => 'PL.TAURON_DYSTRYBUCJA.G11.2026',
                'values' => [
                    'billingCycle.anchor' => '2026-01-01T00:00:00+01:00',
                    'energy.rate' => '0.71',
                ],
            ]],
        ];
    }

    /** @return array<string, mixed> */
    private function g12Configuration(): array {
        return [
            'version' => 1,
            'entries' => [[
                'presetId' => 'PL.TAURON_DYSTRYBUCJA.G12.2026',
                'values' => [
                    'billingCycle.anchor' => '2026-01-01T00:00:00+01:00',
                    'energy.DAY' => '0.98',
                    'energy.NIGHT' => '0.62',
                    'distribution.DAY' => '0.2841',
                    'distribution.NIGHT' => '0.0558',
                ],
            ]],
        ];
    }
}
