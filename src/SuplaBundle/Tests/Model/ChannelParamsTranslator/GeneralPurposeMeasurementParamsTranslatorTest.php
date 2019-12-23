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

namespace SuplaBundle\Tests\Model\ChannelParamsTranslator;

use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Model\ChannelParamsTranslator\ChannelParamConfigTranslator;
use SuplaBundle\Model\ChannelParamsTranslator\GeneralPurposeMeasurementParamsTranslator;

class GeneralPurposeMeasurementParamsTranslatorTest extends TestCase {
    /** @var ChannelParamConfigTranslator */
    private $configTranslator;

    /** @before */
    public function createTranslator() {
        $this->configTranslator = new GeneralPurposeMeasurementParamsTranslator();
    }

    /** @dataProvider unitExamples */
    public function testSettingUnitPrefix($unit, $expectedUnit) {
        $channel = new IODeviceChannel();
        $this->configTranslator->setParamsFromConfig($channel, ['unitPrefix' => $unit]);
        $config = $this->configTranslator->getConfigFromParams($channel);
        $this->assertEquals($expectedUnit, $config['unitPrefix']);
    }

    /** @dataProvider unitExamples */
    public function testSettingUnitSuffix($unit, $expectedUnit) {
        $channel = new IODeviceChannel();
        $this->configTranslator->setParamsFromConfig($channel, ['unitSuffix' => $unit]);
        $config = $this->configTranslator->getConfigFromParams($channel);
        $this->assertEquals($expectedUnit, $config['unitSuffix']);
    }

    public function unitExamples() {
        return [
            [null, null],
            [1, '1'],
            ['', null],
            ['m3', 'm3'],
            ['m³', 'm³'],
            ['m²³', 'm²³'],
            ['alamakota', null],
        ];
    }

    /** @dataProvider paramInfoExamples */
    public function testEncodingParamInfo(array $config, int $expectedValue) {
        $channel = new IODeviceChannel();
        $this->configTranslator->setParamsFromConfig($channel, $config);
        $this->assertEquals($expectedValue, $channel->getParam2());
    }

    public function paramInfoExamples() {
        // @codingStandardsIgnoreStart
        return [
            [['precision' => 3, 'storeMeasurementHistory' => true, 'chartPresentation' => 1, 'chartType' => 0, 'interpolateMeasurements' => false], 0b000011011],
            [['precision' => 3, 'storeMeasurementHistory' => false, 'chartPresentation' => 1, 'chartType' => 0, 'interpolateMeasurements' => false], 0b000010011],
            [['precision' => 3, 'storeMeasurementHistory' => true, 'chartPresentation' => 0, 'chartType' => 0, 'interpolateMeasurements' => false], 0b000001011],
            [['precision' => 3, 'storeMeasurementHistory' => true, 'chartPresentation' => 0, 'chartType' => 1, 'interpolateMeasurements' => false], 0b001001011],
            [['precision' => 3, 'storeMeasurementHistory' => true, 'chartPresentation' => 0, 'chartType' => 0, 'interpolateMeasurements' => true], 0b100001011],
            [['precision' => 5, 'storeMeasurementHistory' => true, 'chartPresentation' => 0, 'chartType' => 0, 'interpolateMeasurements' => false], 0b000001101],
            [['precision' => 0, 'storeMeasurementHistory' => false, 'chartPresentation' => 0, 'chartType' => 0, 'interpolateMeasurements' => false], 0b00000000],
            [['precision' => 0, 'storeMeasurementHistory' => false, 'chartPresentation' => 0, 'chartType' => 2, 'interpolateMeasurements' => false], 0b001000000],
            [['precision' => 0, 'storeMeasurementHistory' => false, 'chartPresentation' => 0, 'chartType' => 20, 'interpolateMeasurements' => false], 0b001000000],
            [['precision' => 0, 'storeMeasurementHistory' => false, 'chartPresentation' => 2, 'chartType' => 0, 'interpolateMeasurements' => false], 0b000010000],
            [['precision' => 0, 'storeMeasurementHistory' => false, 'chartPresentation' => 20, 'chartType' => 0, 'interpolateMeasurements' => false], 0b000010000],
            [['precision' => 20, 'storeMeasurementHistory' => false, 'chartPresentation' => 0, 'chartType' => 0, 'interpolateMeasurements' => false], 0b000000101],
            [['precision' => -4, 'storeMeasurementHistory' => false, 'chartPresentation' => 0, 'chartType' => 0, 'interpolateMeasurements' => false], 0b000000000],
        ];
        // @codingStandardsIgnoreEnd
    }
}
