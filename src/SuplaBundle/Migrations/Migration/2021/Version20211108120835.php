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

use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Migrations\NoWayBackMigration;
use SuplaBundle\Utils\NumberUtils;

class Version20211108120835 extends NoWayBackMigration {
    public function migrate() {
        $icType = ChannelType::IMPULSECOUNTER;
        $icQuery = $this->getConnection()->executeQuery("SELECT id, param1, user_config FROM supla_dev_channel WHERE type = $icType AND param1 > 0");
        while ($icChannel = $icQuery->fetchAssociative()) {
            $id = $icChannel['id'];
            $param1 = $icChannel['param1'];
            $userConfig = json_decode($icChannel['user_config'], true);
            $userConfig['initialValue'] = NumberUtils::maximumDecimalPrecision($param1 / 100, 2);
            $this->addSql(
                'UPDATE supla_dev_channel SET param1=0, user_config=:config WHERE id=:id',
                ['id' => $id, 'config' => json_encode($userConfig)]
            );
        }
    }
}
