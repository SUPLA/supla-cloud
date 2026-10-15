<?php
namespace App\Tests\Model\ResourceAccess;

use App\Enums\ChannelFunction as CF;
use App\Enums\ChannelType as CT;
use App\Model\ResourceAccess\ResourceAccessPermissions;
use PHPUnit\Framework\TestCase;

class ResourceAccessPermissionsTest extends TestCase {
    public static function sources(): array {
        return [
            [CT::THERMOMETER, CF::THERMOMETER, 1, true],
            [CT::THERMOMETERDS18B20, CF::THERMOMETER, 1, false],
            [CT::HUMIDITYANDTEMPSENSOR, CF::HUMIDITYANDTEMPERATURE, 1, true],
            [CT::HUMIDITYANDTEMPSENSOR, CF::THERMOMETER, 1, false],
            [CT::SENSORNO, CF::FLOOD_SENSOR, 1, true],
            [CT::SENSORNO, CF::NONE, 1, false],
            [CT::SENSORNO, CF::FLOOD_SENSOR, 2, false],
            [CT::RELAY, CF::LIGHTSWITCH, 1, true],
            [CT::RELAY, CF::LIGHTSWITCH, 2, true],
            [CT::RELAY, CF::LIGHTSWITCH, 3, true],
            [CT::RELAY, CF::LIGHTSWITCH, 4, false],
            [CT::RELAY2XG5LA1A, CF::LIGHTSWITCH, 2, false],
            [CT::RELAY, CF::NONE, 2, false],
            [CT::RELAY, CF::THERMOMETER, 1, false],
            [CT::HVAC, CF::HVAC_THERMOSTAT, 2, true],
            [CT::VALVEOPENCLOSE, CF::VALVEOPENCLOSE, 2, true],
            [CT::VALVEPERCENTAGE, CF::VALVEPERCENTAGE, 2, true],
            [CT::DIMMER, CF::DIMMER_CCT, 2, true],
            [CT::RGBLEDCONTROLLER, CF::RGBLIGHTING, 2, true],
            [CT::DIMMERANDRGBLED, CF::DIMMER_CCT_AND_RGB, 2, true],
            [CT::GENERAL_PURPOSE_MEASUREMENT, CF::GENERAL_PURPOSE_MEASUREMENT, 1, true],
            [CT::GENERAL_PURPOSE_METER, CF::GENERAL_PURPOSE_METER, 1, true],
            [CT::IMPULSECOUNTER, CF::IC_HEATMETER, 1, true],
            [CT::IMPULSECOUNTER, 350, 1, true],
            [CT::IMPULSECOUNTER, 360, 1, true],
            [CT::CONTAINER, CF::WATER_TANK, 1, true],
            [CT::CONTAINER, CF::WATER_TANK, 2, false],
            [CT::ACTION_TRIGGER, CF::ACTION_TRIGGER, 4, true],
            [CT::ACTION_TRIGGER, CF::ACTION_TRIGGER, 5, false],
            [CT::RELAY, CF::LIGHTSWITCH, 0, false],
            [CT::RELAY, CF::LIGHTSWITCH, 8, false],
        ];
    }

    /** @dataProvider sources */
    public function testRepresentablePermissionSets(int $type, int $function, int $permissions, bool $expected) {
        $this->assertSame($expected, ResourceAccessPermissions::supported(
            $type,
            $function,
            $permissions,
            ['actionTriggerCapabilities' => ['TURN_ON']]
        ));
    }

    public function testActionWithoutPhysicalCapabilitiesIsUnsupported() {
        $this->assertFalse(ResourceAccessPermissions::supported(CT::ACTION_TRIGGER, CF::ACTION_TRIGGER, 4, []));
    }
    public static function streamTypes(): array {
        return [[CT::IMPULSECOUNTER, 350, true], [CT::IMPULSECOUNTER, 360, true], [CT::DIMMER, CF::DIMMER_CCT, true],
            [CT::THERMOMETERDS18B20, CF::THERMOMETER, true], [CT::THERMOMETER, CF::THERMOMETER, true],
            [CT::RELAY2XG5LA1A, CF::LIGHTSWITCH, true], [CT::GENERAL_PURPOSE_MEASUREMENT, CF::GENERAL_PURPOSE_MEASUREMENT, true],
            [CT::ACTION_TRIGGER, CF::ACTION_TRIGGER, false], [CT::THERMOMETER, CF::NONE, false],
            [CT::RELAY, CF::THERMOMETER, false], [999999, CF::THERMOMETER, false]];
    }

    /** @dataProvider streamTypes */
    public function testStreamReadDoesNotRequirePeerRepresentability(int $type, int $function, bool $expected) {
        $this->assertSame($expected, ResourceAccessPermissions::streamReadSupported($type, $function));
    }
}
