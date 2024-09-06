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
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * New channel.config.addToHistory for EM and IC.
 * Split channel.config.numberOfAttemptsToOpenOrClose to numberOfAttemptsToOpen and numberOfAttemptsToClose.
 * Allow adding new flags for a device and channels.
 * Missing version_to_int function added.
 */
class Version20220208164512 extends NoWayBackMigration {
    public function migrate() {
        $this->updateImpulseCountersAddToHistory();
        $this->updateElectricityMetersAddToHistory();
        $this->migrateNumberOfAttemptsToOpenOrClose();
        $this->migrateProcedures();
        $this->addMissingFunction();
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

    private function migrateNumberOfAttemptsToOpenOrClose() {
        $gateFunctions = implode(',', [ChannelFunction::CONTROLLINGTHEGARAGEDOOR, ChannelFunction::CONTROLLINGTHEGATE]);
        $gatesQuery = $this->getConnection()->executeQuery("SELECT id, user_config FROM supla_dev_channel WHERE func IN($gateFunctions)");
        while ($gateChannel = $gatesQuery->fetchAssociative()) {
            $id = $gateChannel['id'];
            $userConfig = json_decode($gateChannel['user_config'], true);
            if (isset($userConfig['numberOfAttemptsToOpenOrClose'])) {
                $numberOfAttempts = intval($userConfig['numberOfAttemptsToOpenOrClose']) ?: 1;
                unset($userConfig['numberOfAttemptsToOpenOrClose']);
                $userConfig['numberOfAttemptsToOpen'] = $numberOfAttempts;
                $userConfig['numberOfAttemptsToClose'] = $numberOfAttempts;
                $this->addSql(
                    'UPDATE supla_dev_channel SET user_config=:config WHERE id=:id',
                    ['id' => $id, 'config' => json_encode($userConfig)]
                );
            }
        }
    }

    private function migrateProcedures() {
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_update_channel_flags`');

        $this->addSql(<<<PROCEDURE
CREATE PROCEDURE `supla_update_channel_flags`(IN `_channel_id` INT, IN `_user_id` INT, IN `_flags` INT)
    NO SQL
UPDATE supla_dev_channel SET flags = flags | _flags WHERE id = _channel_id AND user_id = _user_id
PROCEDURE
        );
    }

    private function addMissingFunction() {
        $this->addSql('DROP FUNCTION IF EXISTS `version_to_int`');

        $this->addSql(<<<FUNC
CREATE  FUNCTION `version_to_int`(`version` VARCHAR(20)) RETURNS int(11)
    NO SQL
BEGIN
DECLARE result INT DEFAULT 0;
DECLARE n INT DEFAULT 0;
DECLARE m INT DEFAULT 0;
DECLARE dot_count INT DEFAULT 0;
DECLARE last_dot_pos INT DEFAULT 0;
DECLARE c VARCHAR(1);

WHILE n < LENGTH(version) DO
    SET n = n+1;
    SET c = SUBSTRING(version, n, 1);
    
    IF c <> '.' AND ( ASCII(c) < 48 OR ASCII(c) > 57 )
      THEN 
         SET result = 0;
         RETURN 0;
      END IF; 
      
    
   IF c = '.' THEN
     SET dot_count = dot_count+1;
     IF dot_count > 2 THEN 
        SET result = 0;
        RETURN 0;
     END IF;
   END IF;

   IF c = '.' OR n = LENGTH(version) THEN

      SET m = n-last_dot_pos-1;
      
      IF c <> '.' THEN
        SET m = n-last_dot_pos;
        SET dot_count = dot_count+1;
      END IF;
      
          SET result = result + POWER(10, 9-dot_count*3) * SUBSTRING(version, last_dot_pos+1, m);
      
      SET last_dot_pos = n;
   END IF;
      
END WHILE;
RETURN result;
END
FUNC
        );
    }
}
