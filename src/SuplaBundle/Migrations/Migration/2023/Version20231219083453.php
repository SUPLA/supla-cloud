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

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Migrate HVAC mode AUTO to HEAT_COOL.
 */
class Version20231219083453 extends NoWayBackMigration {
    public function migrate() {
        $hvacAutoFunctions = implode(',', [ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL]);
        $channels = $this->fetchAll(
            "SELECT id, user_config FROM supla_dev_channel WHERE func IN($hvacAutoFunctions) AND user_config LIKE '%\"AUTO\"%'"
        );
        foreach ($channels as $channelData) {
            $userConfig = json_decode($channelData['user_config'], true);
            if ($userConfig) {
                $changed = false;
                if (isset($userConfig['weeklySchedule']) && isset($userConfig['weeklySchedule']['programSettings'])) {
                    foreach ($userConfig['weeklySchedule']['programSettings'] as &$programSetting) {
                        if (($programSetting['mode'] ?? null) === 'AUTO') {
                            $programSetting['mode'] = 'HEAT_COOL';
                            $changed = true;
                        }
                    }
                }
                if (isset($userConfig['altWeeklySchedule']) && isset($userConfig['altWeeklySchedule']['programSettings'])) {
                    foreach ($userConfig['altWeeklySchedule']['programSettings'] as &$programSetting) {
                        if (($programSetting['mode'] ?? null) === 'AUTO') {
                            $programSetting['mode'] = 'HEAT_COOL';
                            $changed = true;
                        }
                    }
                }
                if ($changed) {
                    $this->addSql('UPDATE supla_dev_channel SET user_config=:user_config WHERE id=:id', [
                        'id' => $channelData['id'],
                        'user_config' => json_encode($userConfig),
                    ]);
                }
            }
        }
    }
}
