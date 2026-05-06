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
 * Move openingTime (param1) and closingTime (param3) to user_config for roller shutters.
 * Add a procedure to expand the list of channel functions.
 */
class Version20240415113159 extends NoWayBackMigration {
    public function migrate() {
        $this->moveOpeningAndClosingTimeToUserConfigForRollerShutters();
    }

    private function moveOpeningAndClosingTimeToUserConfigForRollerShutters() {
        $functions = implode(',', [
            ChannelFunction::CONTROLLINGTHEROLLERSHUTTER,
            ChannelFunction::CONTROLLINGTHEROOFWINDOW,
        ]);
        $channels = $this->fetchAll("SELECT id, param1, param3, user_config FROM supla_dev_channel WHERE func IN($functions)");
        foreach ($channels as $channel) {
            $userConfig = json_decode($channel['user_config'] ?: '{}', true) ?: [];
            $userConfig['openingTimeMs'] = intval($channel['param1']) * 100;
            $userConfig['closingTimeMs'] = intval($channel['param3']) * 100;
            $userConfigJson = json_encode($userConfig);
            $this->addSql('UPDATE supla_dev_channel SET user_config=:user_config, param1=0, param3=0 WHERE id=:id', [
                'id' => $channel['id'],
                'user_config' => $userConfigJson,
            ]);
        }
    }
}
