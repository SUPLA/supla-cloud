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
 * Move invertedLogic (param3) to user_config.
 * Move temperature (param2) and humidity adjustment (param3) to user_config.
 */
class Version20230926065848 extends NoWayBackMigration {
    public function migrate() {
        $this->moveInvertedLogicParamToUserConfig();
        $this->moveTemperatureHumidityAdjustmentsToUserConfig();
    }

    private function moveInvertedLogicParamToUserConfig() {
        $sensorFunctions = implode(',', [
            ChannelFunction::MAILSENSOR,
            ChannelFunction::NOLIQUIDSENSOR,
            ChannelFunction::OPENINGSENSOR_DOOR,
            ChannelFunction::OPENINGSENSOR_GARAGEDOOR,
            ChannelFunction::OPENINGSENSOR_GATE,
            ChannelFunction::OPENINGSENSOR_GATEWAY,
            ChannelFunction::OPENINGSENSOR_ROLLERSHUTTER,
            ChannelFunction::OPENINGSENSOR_ROOFWINDOW,
            ChannelFunction::OPENINGSENSOR_WINDOW,
        ]);
        $sensors = $this->fetchAll("SELECT id, param3, user_config FROM supla_dev_channel WHERE func IN($sensorFunctions)");
        foreach ($sensors as $sensor) {
            $userConfig = json_decode($sensor['user_config'] ?: '{}', true) ?: [];
            $userConfig['invertedLogic'] = boolval($sensor['param3']);
            $userConfigJson = json_encode($userConfig);
            $this->addSql('UPDATE supla_dev_channel SET user_config=:user_config, param3=0 WHERE id=:id', [
                'id' => $sensor['id'],
                'user_config' => $userConfigJson,
            ]);
        }
    }

    private function moveTemperatureHumidityAdjustmentsToUserConfig() {
        $sensorFunctions = implode(',', [
            ChannelFunction::THERMOMETER,
            ChannelFunction::HUMIDITY,
            ChannelFunction::HUMIDITYANDTEMPERATURE,
        ]);
        $sensors = $this->fetchAll("SELECT id, param2, param3, user_config FROM supla_dev_channel WHERE func IN($sensorFunctions)");
        foreach ($sensors as $sensor) {
            $userConfig = json_decode($sensor['user_config'] ?: '{}', true) ?: [];
            if ($sensor['param2']) {
                $userConfig['temperatureAdjustment'] = $sensor['param2'];
            }
            if ($sensor['param3']) {
                $userConfig['humidityAdjustment'] = $sensor['param3'];
            }
            if ($sensor['param2'] || $sensor['param3']) {
                $userConfigJson = json_encode($userConfig);
                $this->addSql('UPDATE supla_dev_channel SET user_config=:user_config, param2=0, param3=0 WHERE id=:id', [
                    'id' => $sensor['id'],
                    'user_config' => $userConfigJson,
                ]);
            }
        }
    }
}
