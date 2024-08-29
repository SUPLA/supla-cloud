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
 * Migrate related meters for relays config from params.
 */
class Version20240824185033 extends NoWayBackMigration {

    public function migrate() {
        $relayFunctions = implode(',', [
            ChannelFunction::POWERSWITCH,
            ChannelFunction::LIGHTSWITCH,
            ChannelFunction::STAIRCASETIMER,
        ]);
        $meterFunctions = implode(',', [
            ChannelFunction::ELECTRICITYMETER,
            ChannelFunction::IC_ELECTRICITYMETER,
            ChannelFunction::IC_GASMETER,
            ChannelFunction::IC_WATERMETER,
            ChannelFunction::IC_HEATMETER,
        ]);
        $relays = $this->fetchAll("SELECT id, func, param1, param2, user_config FROM supla_dev_channel 
                                             WHERE func IN($relayFunctions) AND (param1 > 0 OR param2 > 0)");
        foreach ($relays as $relay) {
            $userConfig = json_decode($relay['user_config'] ?: '{}', true) ?: [];
            $param = $relay['func'] == ChannelFunction::STAIRCASETIMER ? 'param2' : 'param1';
            $relatedMeterId = $relay[$param];
            $userConfig['relatedMeterChannelId'] = intval($relatedMeterId) ?: null;
            $userConfigJson = json_encode($userConfig);
            $this->addSql("UPDATE supla_dev_channel SET user_config=:user_config, {$param}=0 WHERE id=:id", [
                'id' => $relay['id'],
                'user_config' => $userConfigJson,
            ]);
        }
        $this->addSql("UPDATE supla_dev_channel SET param4=0 WHERE func IN($meterFunctions)");
    }
}
