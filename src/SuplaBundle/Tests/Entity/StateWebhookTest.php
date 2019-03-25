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

namespace SuplaBundle\Tests\Entity;

use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\OAuth\ApiClient;
use SuplaBundle\Entity\StateWebhook;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ChannelFunction;

class StateWebhookTest extends \PHPUnit_Framework_TestCase {
    /** @var StateWebhook */
    private $webhook;

    /** @before */
    public function init() {
        $this->webhook = new StateWebhook($this->createMock(ApiClient::class), $this->createMock(User::class));
    }

    public function testSettingFunctions() {
        $this->webhook->setFunctions([ChannelFunction::POWERSWITCH(), ChannelFunction::HUMIDITYANDTEMPERATURE()]);
        $this->assertEquals(
            implode(',', [ChannelFunction::POWERSWITCH, ChannelFunction::HUMIDITYANDTEMPERATURE]),
            EntityUtils::getField($this->webhook, 'functionsIds')
        );
    }
}
