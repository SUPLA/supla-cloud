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

namespace SuplaBundle\Tests\Integration\Command;

use SuplaBundle\Supla\SuplaAutodiscoverMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/** @small */
class SetAutodiscoverIpsCommandIntegrationTest extends IntegrationTestCase {
    public function testSendingIpsToAd() {
        SuplaAutodiscoverMock::clear();
        SuplaAutodiscoverMock::mockResponse('set-broker-ip-addresses', [], 204, 'POST');
        $command = $this->application->find('supla:ad:set-ips');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['1.2.3.4', '2.3.4.5', '', '2.3.4.5', 'y']);
        $exitCode = $commandTester->execute([]);
        $this->assertEquals(0, $exitCode);
        $this->assertCount(1, SuplaAutodiscoverMock::$requests);
        $this->assertEquals(['2.3.4.5', '1.2.3.4'], SuplaAutodiscoverMock::$requests[0]['post']['ips']);
    }
}
