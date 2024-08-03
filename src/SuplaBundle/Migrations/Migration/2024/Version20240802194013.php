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
 * Migrate IC counter config to json.
 */
class Version20240802194013 extends NoWayBackMigration {
    public function migrate() {
        $icFunctions = implode(',', [
            ChannelFunction::IC_ELECTRICITYMETER,
            ChannelFunction::IC_WATERMETER,
            ChannelFunction::IC_HEATMETER,
            ChannelFunction::IC_GASMETER,
        ]);
        $ics = $this->fetchAll("SELECT id, param2, param3, text_param1, text_param2, user_config FROM supla_dev_channel WHERE func IN($icFunctions)");
        foreach ($ics as $ic) {
            $userConfig = json_decode($ic['user_config'] ?: '{}', true) ?: [];
            if (isset($userConfig['initialValue'])) {
                $userConfig['initialValue'] *= 1000;
                $userConfig['initialValue'] = round($userConfig['initialValue']);
            }
            $userConfig['pricePerUnit'] = intval($ic['param2']) ?: $userConfig['pricePerUnit'] ?? 0;
            $userConfig['impulsesPerUnit'] = intval($ic['param3']) ?: $userConfig['impulsesPerUnit'] ?? 0;
            $userConfig['currency'] = ($ic['text_param1'] ?? null) ?: $userConfig['currency'] ?? null;
            $userConfig['unit'] = ($ic['text_param2'] ?? null) ?: $userConfig['unit'] ?? null;
            $userConfigJson = json_encode($userConfig);
            $this->addSql('UPDATE supla_dev_channel SET user_config=:user_config, param2=0, param3=0, text_param1=NULL, text_param2=NULL WHERE id=:id', [
                'id' => $ic['id'],
                'user_config' => $userConfigJson,
            ]);
        }
    }
}
