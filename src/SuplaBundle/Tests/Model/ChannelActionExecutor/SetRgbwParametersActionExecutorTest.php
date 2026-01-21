<?php
namespace SuplaBundle\Tests\Model\ChannelActionExecutor;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Model\ChannelActionExecutor\SetRgbwParametersActionExecutor;
use SuplaBundle\Model\ChannelStateGetter\ColorAndBrightnessChannelStateGetter;
use SuplaBundle\Supla\SuplaServer;
use SuplaBundle\Tests\Integration\Traits\UnitTestHelper;

class SetRgbwParametersActionExecutorTest extends TestCase {
    use UnitTestHelper;

    /**
     * @dataProvider validatingActionParamsProvider
     */
    public function testValidatingActionParams($actionParams, $expected) {
        if (!$expected) {
            $this->expectException(InvalidArgumentException::class);
        }
        $executor = new SetRgbwParametersActionExecutor($this->createMock(ColorAndBrightnessChannelStateGetter::class));
        $subject = $this->createMock(ActionableSubject::class);
        $subject->method('getFunction')->willReturn(ChannelFunction::DIMMERANDRGBLIGHTING());
        $params = $executor->validateAndTransformActionParamsFromApi($subject, $actionParams);
        $this->assertNotNull($params);
        $this->assertEquals($expected, $params);
    }

    public static function validatingActionParamsProvider() {
        return [
            [['hue' => 0], ['color' => '#FF0000']],
            [['hue' => 0, 'color_brightness' => 0], ['color' => '#FF0000', 'color_brightness' => 0]],
            [['color' => 0, 'color_brightness' => 0], false],
            [['color' => 1], ['color' => '#000001']],
            [['color' => 1, 'color_brightness' => 0], ['color' => '#000001', 'color_brightness' => 0]],
            [['color_brightness' => 0], ['color_brightness' => 0]],
            [['hue' => 359, 'color_brightness' => 100], ['color' => '#FF0004', 'color_brightness' => 100]],
            [['hue' => 'random', 'color_brightness' => 100], ['color' => 'random', 'color_brightness' => 100]],
            [['hue' => 'white', 'color_brightness' => 100], ['color' => '#FFFFFF', 'color_brightness' => 100]],
            [['color' => 'random'], ['color' => 'random']],
            [['color' => 0xFF, 'color_brightness' => 100], ['color' => '#0000FF', 'color_brightness' => 100]],
            [['color' => '0xFFFFFF', 'color_brightness' => 100], ['color' => '#FFFFFF', 'color_brightness' => 100]],
            [['color' => '#FFFFFF', 'color_brightness' => 100], ['color' => '#FFFFFF', 'color_brightness' => 100]],
            [['color' => '0xFFFFFFF', 'color_brightness' => 100], false],
            [['color' => '0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF', 'color_brightness' => 100], false],
            [['color' => '0xFFFXFF', 'color_brightness' => 100], false],
            [['brightness' => 22], ['brightness' => 22]],
            [['brightness' => 100], ['brightness' => 100]],
            [['brightness' => 50, 'hue' => 359, 'color_brightness' => 100], ['brightness' => 50, 'color' => '#FF0004', 'color_brightness' => 100]],
            [
                ['brightness' => '50', 'hue' => '359', 'color_brightness' => '100'],
                ['brightness' => 50, 'color' => '#FF0004', 'color_brightness' => 100],
            ],
            [['blabla' => 50, 'hue' => 359, 'color_brightness' => 100], false],
            [['hue' => 360, 'color_brightness' => 100], false],
            [['hue' => -1, 'color_brightness' => 100], false],
            [['hue' => 0, 'color_brightness' => 101], false],
            [['hue' => 0, 'color_brightness' => -1], false],
            [['hue' => 'black', 'color_brightness' => 100], false],
            [['brightness' => -1], false],
            [['brightness' => 101], false],
            [['brightness' => 'ala'], false],
            [['brightness' => 100, 'alexaCorrelationToken' => 'abcd'], false],
            [['brightness' => 100, 'googleRequestId' => 'abcd'], false],
            'hsv valid' => [['hsv' => ['hue' => 100, 'saturation' => 50, 'value' => 50]], ['color' => '#558040', 'color_brightness' => 50]],
            'hsv with brightness' => [
                ['hsv' => ['hue' => 100, 'saturation' => 50, 'value' => 50], 'brightness' => 80],
                ['color' => '#558040', 'color_brightness' => 50, 'brightness' => 80],
            ],
            'hsv with value 150 (x)' => [['hsv' => ['hue' => 100, 'saturation' => 50, 'value' => 150]], false],
            'hsv with no value (x)' => [['hsv' => ['hue' => 100, 'saturation' => 50]], false],
            'hsv with cb (x)' => [['hsv' => ['hue' => 100, 'saturation' => 50, 'value' => 50], 'color_brightness' => 50], false],
            'hsv with color (x)' => [['hsv' => ['hue' => 100, 'saturation' => 50, 'value' => 50], 'color' => 1], false],
            'rgb valid' => [['rgb' => ['red' => 100, 'green' => 50, 'blue' => 50]], ['color' => '#643232', 'color_brightness' => 39]],
            'rgb with brightness' => [
                ['rgb' => ['red' => 100, 'green' => 50, 'blue' => 50], 'brightness' => 50],
                ['color' => '#643232', 'color_brightness' => 39, 'brightness' => 50],
            ],
            'rgb green 256 (x)' => [['rgb' => ['red' => 100, 'green' => 256, 'blue' => 50]], false],
            'rgb with cb (x)' => [['rgb' => ['red' => 100, 'green' => 50, 'blue' => 50], 'color_brightness' => 50], false],
            'hsv and rgb (x)' => [
                ['rgb' => ['red' => 100, 'green' => 50, 'blue' => 50], 'hsv' => ['hue' => 100, 'saturation' => 50, 'value' => 50]],
                false,
            ],
            [['hue' => 0, 'turnOnOff' => true], ['color' => '#FF0000', 'turnOnOff' => 3]],
            [['hue' => 0, 'turnOnOff' => false], ['color' => '#FF0000', 'turnOnOff' => 0]],
            [['hue' => 0, 'turnOnOff' => 1], ['color' => '#FF0000', 'turnOnOff' => 1]],
            [['white_temperature' => 33], ['white_temperature' => 33]],
            [['white_temperature' => '33'], ['white_temperature' => 33]],
            [['brightness' => 55, 'white_temperature' => '33'], ['brightness' => 55, 'white_temperature' => 33]],
            [['white_temperature' => 101], false],
        ];
    }

    public function testConvertingStringColor() {
        $executor = new SetRgbwParametersActionExecutor($this->createMock(ColorAndBrightnessChannelStateGetter::class));
        $subject = $this->createMock(ActionableSubject::class);
        $validated = $executor->validateAndTransformActionParamsFromApi($subject, ['color' => '12', 'color_brightness' => '56']);
        $this->assertSame('#00000C', $validated['color']);
        $this->assertSame(56, $validated['color_brightness']);
    }

    public function testConvertingHexColor() {
        $executor = new SetRgbwParametersActionExecutor($this->createMock(ColorAndBrightnessChannelStateGetter::class));
        $subject = $this->createMock(ActionableSubject::class);
        $validated = $executor->validateAndTransformActionParamsFromApi($subject, ['color' => '0xFFCC77', 'color_brightness' => '56']);
        $this->assertSame('#FFCC77', $validated['color']);
    }

    /** @dataProvider exampleRgbwParameters */
    public function testSettingRgbwParameters(
        array $params,
        string $expectedCommand,
        int $functionId = ChannelFunction::DIMMERANDRGBLIGHTING
    ) {
        $executor = new SetRgbwParametersActionExecutor();
        $suplaServer = $this->createMock(SuplaServer::class);
        $executor->setSuplaServer($suplaServer);
        $suplaServer->expects($this->once())->method('executeCommand')->willReturnCallback(
            function (string $command) use ($expectedCommand) {
                if (!str_starts_with($expectedCommand, 'SET-')) {
                    $expectedCommand = 'SET-RGBW-VALUE:1,1,1,' . $expectedCommand;
                }
                $this->assertEquals($expectedCommand, $command);
                return 'OK:true';
            }
        );
        $channel = new IODeviceChannel();
        $channel->setFunction(new ChannelFunction($functionId));
        EntityUtils::setField($channel, 'id', 1);
        EntityUtils::setField($channel, 'user', $this->createEntityMock(User::class));
        EntityUtils::setField($channel, 'iodevice', $this->createEntityMock(IODevice::class));
        $params = $executor->validateAndTransformActionParamsFromApi($channel, $params);
        $executor->execute($channel, $params);
    }

    public static function exampleRgbwParameters() {
        return [
            [['hue' => 0], '16711680,-1,-1,-1,-1,-1'],
            [['hue' => 0, 'color_brightness' => 0], '16711680,0,-1,-1,-1,-1'],
            [['color_brightness' => 33], '-1,33,-1,-1,-1,-1'],
            [['hue' => 'white', 'color_brightness' => 12], '16777215,12,-1,-1,-1,-1'],
            [['color' => '0xFF0000'], '16711680,-1,-1,-1,-1,-1'],
            [['rgb' => ['red' => 255, 'green' => 0, 'blue' => 0]], '16711680,100,-1,-1,-1,-1'],
            [['color' => '0xAA0000'], '11141120,-1,-1,-1,-1,-1'],
            [['color' => '0xAA0000', 'color_brightness' => 70], '11141120,70,-1,-1,-1,-1'],
            [['color' => '0xAA0000', 'brightness' => 70], '11141120,-1,70,-1,-1,-1'],
            [['rgb' => ['red' => 170, 'green' => 0, 'blue' => 0]], '11141120,67,-1,-1,-1,-1'],
            [['color' => '0xFF0000', 'brightness' => 40], '16711680,-1,40,-1,-1,-1'],
            [['rgb' => ['red' => 255, 'green' => 0, 'blue' => 0], 'brightness' => 40], '16711680,100,40,-1,-1,-1'],
            [['hsv' => ['hue' => 0, 'saturation' => 100, 'value' => 100]], '16711680,100,-1,-1,-1,-1'],
            [['hsv' => ['hue' => 0, 'saturation' => 100, 'value' => 60]], '10027008,60,-1,-1,-1,-1'],
            [['hsv' => ['hue' => 0, 'saturation' => 100, 'value' => 60]], '10027008,60,-1,-1,-1,-1'],
            [['color_brightness' => 40], '-1,40,-1,-1,-1,-1'],
            [['color' => 'random'], 'SET-RAND-RGBW-VALUE:1,1,1,-1,-1,-1,-1,-1'],
            [['hue' => 'random', 'color_brightness' => 88], 'SET-RAND-RGBW-VALUE:1,1,1,88,-1,-1,-1,-1'],
            [['color' => 'random', 'color_brightness' => 98], 'SET-RAND-RGBW-VALUE:1,1,1,98,-1,-1,-1,-1'],
            [['hue' => 0, 'color_brightness' => 0, 'turnOnOff' => true], '16711680,0,-1,3,-1,-1', ChannelFunction::DIMMERANDRGBLIGHTING],
            [['hue' => 0, 'turnOnOff' => 2], '16711680,-1,-1,2,-1,-1', ChannelFunction::DIMMERANDRGBLIGHTING],
            [['hue' => 0, 'turnOnOff' => 1], '16711680,-1,-1,1,-1,-1', ChannelFunction::DIMMERANDRGBLIGHTING],
            [['hue' => 0, 'turnOnOff' => true], '16711680,-1,-1,1,-1,-1', ChannelFunction::DIMMER],
            [['hue' => 0, 'turnOnOff' => 2], '16711680,-1,-1,1,-1,-1', ChannelFunction::DIMMER],
            [['hue' => 0, 'turnOnOff' => 23], '16711680,-1,-1,1,-1,-1', ChannelFunction::DIMMER],
            [['hue' => 0, 'turnOnOff' => true], '16711680,-1,-1,2,-1,-1', ChannelFunction::RGBLIGHTING],
            [['hue' => 0, 'turnOnOff' => 1], '16711680,-1,-1,2,-1,-1', ChannelFunction::RGBLIGHTING],
            [['hue' => 0, 'turnOnOff' => false], '16711680,-1,-1,0,-1,-1'],
            [['white_temperature' => 33], '-1,-1,-1,-1,-1,33'],
            [['brightness' => 55, 'white_temperature' => 33], '-1,-1,55,-1,-1,33'],
            [['color' => '#87cefa', 'white_temperature' => 33], '8900346,-1,-1,-1,-1,33'],
        ];
    }
}
