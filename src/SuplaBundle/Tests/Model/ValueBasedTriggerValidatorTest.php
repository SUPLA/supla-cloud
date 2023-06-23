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

    public function validTriggers() {
        return [
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"lt": 20, "name": "temperature", "resume": {"ge": 21}}}'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"ge": 20, "name": "temperature", "resume": {"lt": 19}}}'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"ge": 20, "name": "temperature", "resume": {"lt": 20}}}'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"ge": 20, "name": "temperature"}}'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"ge": 20}}'],
            [ChannelFunction::HUMIDITY(), '{"on_change_to": {"ge": 20}}'],
            [ChannelFunction::HUMIDITY(), '{"on_change_to": {"ge": 20, "name": "humidity"}}'],
            [ChannelFunction::HUMIDITYANDTEMPERATURE(), '{"on_change_to": {"ge": 20, "name": "humidity"}}'],
            [ChannelFunction::DEPTHSENSOR(), '{"on_change_to": {"ge": 20}}'],
            [ChannelFunction::DEPTHSENSOR(), '{"on_change_to": {"ge": 20, "resume": {"lt": 10}}}'],
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

    public function invalidTriggers() {
        return [
            [ChannelFunction::THERMOMETER(), '{}', 'Missing on_change_to'],
            [ChannelFunction::THERMOMETER(), '{"unicorn": {}}', 'Missing on_change_to'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": ""}', 'on_change_to must be an object'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {}}', 'Unrecognized trigger format'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"lt": 20, "gt": 30}}', 'only one condition'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"lt": 20}, "unicorn": ""}', 'No extra keys'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"lt": "ala"}}', 'must be numeric'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"lt": 20, "resume": ""}}', 'Invalid resume'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"lt": 20, "resume": {}}}', 'Invalid resume'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"lt": 20, "resume": {"le": 20}}}', 'must have a ge operator'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"lt": 20, "name": "temperature", "resume": {"ge": 19}}}', 'must be greater'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"gt": 20, "name": "temperature", "resume": {"le": 21}}}', 'must be less'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"gt": 20, "name": "unicorn", "resume": {"le": 20}}}', 'Unsupported'],
            [ChannelFunction::THERMOMETER(), '{"on_change_to": {"gt": 20, "name": "humidity", "resume": {"le": 20}}}', 'Unsupported'],
            [ChannelFunction::DEPTHSENSOR(), '{"on_change_to": {"gt": 20, "name": "depth", "resume": {"le": 20}}}', 'not required'],
            [ChannelFunction::HUMIDITYANDTEMPERATURE(), '{"on_change_to": {"gt": 20}}', 'Missing trigger field'],
            [ChannelFunction::MAILSENSOR(), '{"on_change_to": {"gt": 20}}', 'trigger unsupported for this function'],
        ];
    }
}
