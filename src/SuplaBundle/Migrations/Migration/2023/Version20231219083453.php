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
 * Add supla_oauth_add_token_for_alexa_discover procedure.
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

        $this->addSql(<<<PROCEDURE
            CREATE PROCEDURE `supla_oauth_add_token_for_alexa_discover`(IN `_user_id` INT)
BEGIN
   DECLARE characters VARCHAR(62) DEFAULT 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
   DECLARE i INT DEFAULT 1;
   DECLARE svr VARCHAR(168) DEFAULT '';
   DECLARE token VARCHAR(255) DEFAULT NULL;
   DECLARE client_id INT DEFAULT 0;

   SELECT t.client_id, SUBSTRING_INDEX(t.token, '.', -1) INTO client_id, svr FROM `supla_oauth_refresh_tokens` t 
          WHERE t.user_id = _user_id AND t.client_id IN (SELECT id FROM `supla_oauth_clients` WHERE name = 'AMAZON ALEXA') 
          ORDER BY expires_at DESC LIMIT 1;

   
   SELECT t.token INTO token FROM `supla_oauth_access_tokens` t WHERE t.client_id = client_id AND name = 'ALEXA DISCOVER' AND user_id = _user_id AND expires_at - UNIX_TIMESTAMP() >= 60;

   IF token IS NOT NULL THEN
     SELECT token;
   ELSE
     SET token = '';
     
     WHILE i <= 86 DO
        SET token = CONCAT(token, SUBSTRING(characters, FLOOR(RAND() * 62) + 1, 1));
        SET i = i + 1;
     END WHILE;
    
     IF client_id > 0 THEN
       SET token = CONCAT(token, '.', svr);
    
       INSERT INTO `supla_oauth_access_tokens`(`client_id`, `user_id`, `token`, `expires_at`, `scope`, `name`, `access_id`, `issuer_browser_string`) VALUES 
           (client_id, _user_id, token, UNIX_TIMESTAMP()+3600, 'channels_r iodevices_r locations_r account_r scenes_r', 'ALEXA DISCOVER', NULL, 'supla-server');
    
       SELECT token;
     END IF;
   END IF;
END
PROCEDURE
        );
    }
}
