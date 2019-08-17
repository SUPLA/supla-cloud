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

namespace SuplaBundle\Tests\Enums;

use Psr\Log\LoggerInterface;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ElectricityMeterSupportBits;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Supla\SuplaServerMockCommandsCollector;
use SuplaBundle\Tests\Integration\Traits\UnitTestHelper;

class ElectricityMeterSupportBitsTestBitsTest extends \PHPUnit_Framework_TestCase {
    use UnitTestHelper;

    /** @var SuplaServerMock */
    private $suplaServer;
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $channel;

    /** @before */
    public function init() {
        $commandsCollector = $this->createMock(SuplaServerMockCommandsCollector::class);
        $this->suplaServer = new SuplaServerMock($commandsCollector, $this->createMock(LoggerInterface::class));
        $channel = $this->createMock(IODeviceChannel::class);
        $channel->method('getUser')->willReturn($this->createEntityMock(User::class));
        $channel->method('getIoDevice')->willReturn($this->createEntityMock(IODevice::class));
        $this->channel = $channel;
    }

    /** @dataProvider supportedFunctionsTestCases */
    public function testNullifyingUnsupportedFeatures(int $supportMask, array $expectNotNulls) {
        $state = $this->suplaServer->getElectricityMeterValue($this->channel);
        $state = ElectricityMeterSupportBits::nullifyUnsupportedFeatures($supportMask, $state);
        $this->assertGreaterThan(30, count($state));
        unset($state['support']);
        unset($state['totalCost']);
        unset($state['pricePerUnit']);
        unset($state['currency']);
        foreach ($state as $key => $value) {
            if (in_array($key, $expectNotNulls)) {
                $this->assertNotNull($value, 'The value of ' . $key . ' should not be null.');
            } else {
                $this->assertNull($value, 'The value of ' . $key . ' should be null.');
            }
        }
    }

    public function supportedFunctionsTestCases() {
        return [
            [0, []],
            [ElectricityMeterSupportBits::CURRENT, ['currentPhase1', 'currentPhase2', 'currentPhase3']],
            [
                ElectricityMeterSupportBits::CURRENT | ElectricityMeterSupportBits::FREQUENCY,
                ['frequency', 'currentPhase1', 'currentPhase2', 'currentPhase3'],
            ],
            [
                ElectricityMeterSupportBits::FORWARD_REACTIVE_ENERGY,
                ['totalForwardReactiveEnergyPhase1', 'totalForwardReactiveEnergyPhase2', 'totalForwardReactiveEnergyPhase3'],
            ],
        ];
    }

    public function testEveryBitIsExclusive() {
        $bitsSum = 0;
        foreach (ElectricityMeterSupportBits::values() as $bit) {
            $newBitsSum = $bitsSum | $bit->getValue();
            $this->assertNotEquals($newBitsSum, $bitsSum, 'Non exclusive detected on ' . $bit->getKey());
            $bitsSum = $newBitsSum;
        }
    }
}
