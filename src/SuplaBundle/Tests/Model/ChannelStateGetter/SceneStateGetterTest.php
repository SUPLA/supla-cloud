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

namespace SuplaBundle\Tests\Model\ChannelStateGetter;

use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\Main\Scene;
use SuplaBundle\Enums\SceneInitiatiorType;
use SuplaBundle\Model\ChannelStateGetter\SceneStateGetter;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Tests\Integration\Traits\UnitTestHelper;

class SceneStateGetterTest extends TestCase {
    use UnitTestHelper;

    /** @dataProvider stateExamples */
    public function testGettingState(array $suplaServerSummaryResponse, array $expectedState) {
        $sceneStateGetter = new SceneStateGetter();
        $suplaServer = $this->createMock(SuplaServerMock::class);
        $suplaServer->method('getSceneSummary')->willReturn($suplaServerSummaryResponse);
        $sceneStateGetter->setSuplaServer($suplaServer);
        $scene = $this->createEntityMock(Scene::class);
        $state = $sceneStateGetter->getState($scene);
        $this->assertEquals($expectedState, $state);
    }

    public static function stateExamples() {
        return [
            [[0, 0, 0, 0, 0], ['executing' => false]],
            [
                [SceneInitiatiorType::DEVICE, 22, base64_encode('Unicorn'), 10, 100],
                [
                    'executing' => true,
                    'initiatorTypeId' => SceneInitiatiorType::DEVICE,
                    'initiatorType' => 'DEVICE',
                    'initiatorId' => 22,
                    'initiatorName' => 'Unicorn',
                    'millisecondsFromStart' => 10,
                    'millisecondsToEnd' => 100,
                ],
            ],
        ];
    }
}
