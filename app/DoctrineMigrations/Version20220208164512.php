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

namespace Supla\Migrations;

use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;

/**
 * New channel.config.addToHistory for EM and IC.
 */
class Version20220208164512 extends NoWayBackMigration {
    public function migrate() {
        $this->updateImpulseCountersAddToHistory();
        $this->updateElectricityMetersAddToHistory();
    }

    private function updateImpulseCountersAddToHistory() {
        $icType = ChannelType::IMPULSECOUNTER;
        $icQuery = $this->getConnection()->executeQuery("SELECT id, user_config FROM supla_dev_channel WHERE type = $icType");
        while ($icChannel = $icQuery->fetchAssociative()) {
            $id = $icChannel['id'];
            $userConfig = json_decode($icChannel['user_config'], true);
            $initialValue = $userConfig['initialValue'] ?? 0;
            $userConfig['addToHistory'] = $initialValue > 0;
            $this->addSql(
                'UPDATE supla_dev_channel SET user_config=:config WHERE id=:id',
                ['id' => $id, 'config' => json_encode($userConfig)]
            );
        }
    }

    private function updateElectricityMetersAddToHistory() {
        $ecFunction = ChannelFunction::ELECTRICITYMETER;
        $ecQuery = $this->getConnection()->executeQuery("SELECT id, user_config FROM supla_dev_channel WHERE func = $ecFunction");
        while ($ecChannel = $ecQuery->fetchAssociative()) {
            $id = $ecChannel['id'];
            $userConfig = json_decode($ecChannel['user_config'], true);
            $initialValues = $userConfig['electricityMeterInitialValues'] ?? [];
            $nonZeroInitialValue = array_filter($initialValues);
            $userConfig['addToHistory'] = count($nonZeroInitialValue) > 0;
            $this->addSql(
                'UPDATE supla_dev_channel SET user_config=:config WHERE id=:id',
                ['id' => $id, 'config' => json_encode($userConfig)]
            );
        }
    }
}
