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

namespace SuplaBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Model\ValueBasedTriggerValidator;
use SuplaBundle\Tests\Integration\Traits\UnitTestHelper;

class ValueBasedTriggerValidatorTest extends TestCase {
    use UnitTestHelper;

    /** @dataProvider validTriggers */
    public function testValidTriggers(ChannelFunction $channelFunction, string $trigger) {
        $validator = new ValueBasedTriggerValidator();
        $triggerDef = json_decode($trigger, true);
        $channel = $this->createSubjectMock(IODeviceChannel::class);
        $channel->method('getFunction')->willReturn($channelFunction);
        $validator->validate($channel, $triggerDef);
        $this->assertTrue(true); // no exception thrown
    }

    public static function validTriggers() {
        return [
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"lt": 20, "name": "temperature", "resume": {"ge": 21}}}'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"ge": 20, "name": "temperature", "resume": {"lt": 19}}}'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"ge": 20, "name": "temperature", "resume": {"lt": 20}}}'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"ge": 20, "name": "temperature"}}'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"eq": "on", "name": "battery_powered"}}'],
            [ChannelFunction::HUMIDITY(), '{"on_change_to": {"ge": 20, "name": "humidity"}}'],
            [ChannelFunction::HUMIDITYANDTEMPERATURE(), '{"on_change_to": {"ge": 20, "name": "humidity"}}'],
            [ChannelFunction::HUMIDITYANDTEMPERATURE(), '{"on_change_to": {"ge": 20, "name": "battery_level", "resume": {"lt": 20}}}'],
            [ChannelFunction::DEPTHSENSOR(), '{"on_change_to": {"ge": 20}}'],
            [ChannelFunction::DEPTHSENSOR(), '{"on_change_to": {"ge": 20, "resume": {"lt": 10}}}'],
            [ChannelFunction::MAILSENSOR(), '{"on_change_to": {"eq": "hi"}}'],
            [ChannelFunction::OPENINGSENSOR_GARAGEDOOR(), '{"on_change_to": {"ne": "closed"}}'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"eq": 20, "name": "temperature"}}'],
            [ChannelFunction::THERMOMETER(), '{"on_change": {"name": "temperature"}}'],
            [ChannelFunction::HUMIDITYANDTEMPERATURE(), '{"on_change": {"name": "temperature"}}'],
            [ChannelFunction::OPENINGSENSOR_GARAGEDOOR(), '{"on_change": {}}'],
            [ChannelFunction::CONTROLLINGTHEROOFWINDOW(), '{"on_change": {}}'],
            [ChannelFunction::DIMMER(), '{"on_change": {"name": "brightness"}}'],
            [ChannelFunction::VALVEOPENCLOSE(), '{"on_change_to": {"eq": "closed"}}'],
            [ChannelFunction::VALVEOPENCLOSE(), '{"on_change_to": {"eq": "closed", "name": "manually_closed"}}'],
            [ChannelFunction::GENERAL_PURPOSE_METER(), '{"on_change_to": {"eq": 20}}'],
            [ChannelFunction::GENERAL_PURPOSE_METER(), '{"on_change_to": {"gt": 20}}'],
            [ChannelFunction::GENERAL_PURPOSE_METER(), '{"on_change": {}}'],
            [ChannelFunction::GENERAL_PURPOSE_MEASUREMENT(), '{"on_change_to": {"gt": 20}}'],
            [ChannelFunction::GENERAL_PURPOSE_MEASUREMENT(), '{"on_change_to": {"gt": 20, "resume": {"le": 10}}}'],
            [ChannelFunction::GENERAL_PURPOSE_MEASUREMENT(), '{"on_change": {}}'],
            [ChannelFunction::CONTROLLINGTHEROLLERSHUTTER(), '{"on_change": {}}'],
            [ChannelFunction::CONTROLLINGTHEROLLERSHUTTER(), '{"on_change_to": {"ge": 20, "resume": {"lt": 10}}}'],
            [ChannelFunction::CONTROLLINGTHEROLLERSHUTTER(), '{"on_change_to": {"eq": "on", "name": "calibration_failed"}}'],
            [ChannelFunction::CONTROLLINGTHEROLLERSHUTTER(), '{"on_change_to": {"eq": "on", "name": "motor_problem"}}'],
            [ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL(), '{"on_change_to": {"eq": "on", "name": "clock_error"}}'],
            [ChannelFunction::HVAC_DOMESTIC_HOT_WATER(), '{"on_change_to": {"ge": 20, "name": "battery_level", "resume": {"lt": 20}}}'],
        ];
    }

    /** @dataProvider invalidTriggers */
    public function testInvalidTriggers(ChannelFunction $channelFunction, string $trigger, string $expectedMessage = '') {
        $this->expectException(\InvalidArgumentException::class);
        if ($expectedMessage) {
            $this->expectExceptionMessage($expectedMessage);
        }
        $this->testValidTriggers($channelFunction, $trigger);
    }

    public static function invalidTriggers() {
        return [
            [ChannelFunction::THERMOMETER(), '{}', 'Invalid trigger'],
            [ChannelFunction::THERMOMETER(), '{"unicorn": {}}', 'Missing on_change_to'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": ""}', 'on_change_to must be an object'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"name": "temperature"}}', 'Unrecognized trigger format'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"name": "temperature", "lt": 20, "gt": 30}}', 'only one condition'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"name": "temperature", "lt": 20}, "unicorn": ""}', 'Only one on_change'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"name": "temperature", "lt": "ala"}}', 'must be numeric'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"name": "temperature", "lt": 20, "resume": ""}}', 'Invalid resume'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"name": "temperature", "lt": 20, "resume": {}}}', 'Invalid resume'],
            [
                ChannelFunction::THERMOMETER(),
                '{"on_change_to": {"name": "temperature", "lt": 20, "resume": {"le": 20}}}', 'must have a ge operator',
            ],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"lt": 20, "name": "temperature", "resume": {"ge": 19}}}', 'must be greate'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"gt": 20, "name": "temperature", "resume": {"le": 21}}}', 'must be less'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"gt": 20, "name": "unicorn", "resume": {"le": 20}}}', 'Unsupported'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"gt": 20, "name": "humidity", "resume": {"le": 20}}}', 'Unsupported'],
            [ChannelFunction::DEPTHSENSOR(), '{"on_change_to": {"gt": 20, "name": "depth", "resume": {"le": 20}}}', 'not required'],
            [ChannelFunction::HUMIDITYANDTEMPERATURE(), '{"on_change_to": {"gt": 20}}', 'Missing trigger field'],
            [ChannelFunction::MAILSENSOR(), '{"on_change_to": {"gt": 20}}', 'trigger unsupported for this function'],
            [ChannelFunction::MAILSENSOR(), '{"on_change_to": {"eq": "lo", "ne": "lo"}}', 'only one condition'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"name": "temperature", "eq": "lo"}}', 'must be numeric'],
            [ChannelFunction::MAILSENSOR(), '{"on_change_to": {"eq": "blabla"}}', 'comparison value'],
            [ChannelFunction::THERMOMETER(), '{"on_change": {"unicorn": "rainbow"}}', 'Only name can be defined'],
            [ChannelFunction::THERMOMETER(), '{"on_change": {"name": "rainbow"}}', 'Unsupported'],
            [ChannelFunction::DIMMER(), '{"on_change": {}}'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"ge": 20}}'],
            [ChannelFunction::HUMIDITY(), '{"on_change_to": {"ge": 20}}'],
            [ChannelFunction::THERMOMETER(), '{"on_change": {}}'],
        ];
    }
}
