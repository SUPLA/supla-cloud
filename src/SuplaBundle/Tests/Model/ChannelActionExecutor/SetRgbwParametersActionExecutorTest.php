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
    public function testValidatingActionParams($actionParams, bool $expectValid) {
        if (!$expectValid) {
            $this->expectException(InvalidArgumentException::class);
        }
        $executor = new SetRgbwParametersActionExecutor($this->createMock(ColorAndBrightnessChannelStateGetter::class));
        $params = $executor->validateAndTransformActionParamsFromApi($this->createMock(ActionableSubject::class), $actionParams);
        $this->assertNotNull($params);
    }

    public static function validatingActionParamsProvider() {
        return [
            [['hue' => 0, 'color_brightness' => 0], true],
            [['color' => 0, 'color_brightness' => 0], false],
            [['color' => 1, 'color_brightness' => 0], true],
            [['hue' => 359, 'color_brightness' => 100], true],
            [['hue' => 'random', 'color_brightness' => 100], true],
            [['hue' => 'white', 'color_brightness' => 100], true],
            [['color' => 'random', 'color_brightness' => 100], true],
            [['color' => 0xFF, 'color_brightness' => 100], true],
            [['color' => '0xFFFFFF', 'color_brightness' => 100], true],
            [['color' => '0xFFFFFFF', 'color_brightness' => 100], false],
            [['color' => '0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF', 'color_brightness' => 100], false],
            [['color' => '0xFFFXFFFF', 'color_brightness' => 100], false],
            [['brightness' => 0], true],
            [['brightness' => 100], true],
            [['brightness' => 50, 'hue' => 359, 'color_brightness' => 100], true],
            [['brightness' => '50', 'hue' => '359', 'color_brightness' => '100'], true],
            [['blabla' => 50, 'hue' => 359, 'color_brightness' => 100], false],
            [['hue' => 360, 'color_brightness' => 100], false],
            [['hue' => -1, 'color_brightness' => 100], false],
            [['hue' => 0, 'color_brightness' => 101], false],
            [['hue' => 0, 'color_brightness' => -1], false],
            [['hue' => 0], true],
            [['color' => 1], true],
            [['hue' => 'black', 'color_brightness' => 100], false],
            [['brightness' => -1], false],
            [['brightness' => 101], false],
            [['brightness' => 'ala'], false],
            [['brightness' => 100, 'alexaCorrelationToken' => 'abcd'], false],
            [['brightness' => 100, 'googleRequestId' => 'abcd'], false],
            'hsv valid' => [['hsv' => ['hue' => 100, 'saturation' => 50, 'value' => 50]], true],
            'hsv with brightness' => [['hsv' => ['hue' => 100, 'saturation' => 50, 'value' => 50], 'brightness' => 80], true],
            'hsv with value 150 (x)' => [['hsv' => ['hue' => 100, 'saturation' => 50, 'value' => 150]], false],
            'hsv with no value (x)' => [['hsv' => ['hue' => 100, 'saturation' => 50]], false],
            'hsv with cb (x)' => [['hsv' => ['hue' => 100, 'saturation' => 50, 'value' => 50], 'color_brightness' => 50], false],
            'hsv with color (x)' => [['hsv' => ['hue' => 100, 'saturation' => 50, 'value' => 50], 'color' => 1], false],
            'rgb valid' => [['rgb' => ['red' => 100, 'green' => 50, 'blue' => 50]], true],
            'rgb with brightness' => [['rgb' => ['red' => 100, 'green' => 50, 'blue' => 50], 'brightness' => 50], true],
            'rgb green 256 (x)' => [['rgb' => ['red' => 100, 'green' => 256, 'blue' => 50]], false],
            'rgb with cb (x)' => [['rgb' => ['red' => 100, 'green' => 50, 'blue' => 50], 'color_brightness' => 50], false],
            'hsv and rgb (x)' => [
                ['rgb' => ['red' => 100, 'green' => 50, 'blue' => 50], 'hsv' => ['hue' => 100, 'saturation' => 50, 'value' => 50]],
                false,
            ],
            [['hue' => 0, 'turnOnOff' => true], true],
            [['hue' => 0, 'turnOnOff' => false], true],
            [['hue' => 0, 'turnOnOff' => 1], true],
        ];
    }

    public function testConvertingStringColorToInt() {
        $executor = new SetRgbwParametersActionExecutor($this->createMock(ColorAndBrightnessChannelStateGetter::class));
        $subject = $this->createMock(ActionableSubject::class);
        $validated = $executor->validateAndTransformActionParamsFromApi($subject, ['color' => '12', 'color_brightness' => '56']);
        $this->assertSame(12, $validated['color']);
        $this->assertSame(56, $validated['color_brightness']);
    }

    public function testConvertingHexColorToInt() {
        $executor = new SetRgbwParametersActionExecutor($this->createMock(ColorAndBrightnessChannelStateGetter::class));
        $subject = $this->createMock(ActionableSubject::class);
        $validated = $executor->validateAndTransformActionParamsFromApi($subject, ['color' => '0xFFCC77', 'color_brightness' => '56']);
        $this->assertSame(0xFFCC77, $validated['color']);
    }

    /** @dataProvider exampleRgbwParameters */
    public function testSettingRgbwParameters(
        array $params,
        string $expectedCommand,
        array $currentState = [],
        int $functionId = ChannelFunction::DIMMERANDRGBLIGHTING
    ) {
        $stateGetter = $this->createMock(ColorAndBrightnessChannelStateGetter::class);
        $stateGetter->method('getState')->willReturn($currentState);
        $executor = new SetRgbwParametersActionExecutor($stateGetter);
        $suplaServer = $this->createMock(SuplaServer::class);
        $executor->setSuplaServer($suplaServer);
        $suplaServer->expects($this->once())->method('executeCommand')->willReturnCallback(
            function (string $command) use ($expectedCommand) {
                if (strpos($expectedCommand, 'SET-') !== 0) {
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
        $executor->execute($channel, $params);
    }

    public static function exampleRgbwParameters() {
        return [
            [['hue' => 0, 'color_brightness' => 0], '16711680,0,0,0'],
            [['hue' => 'white', 'color_brightness' => 12], '16777215,12,0,0'],
            [['hue' => 0], '16711680,0,0,0'],
            [['hue' => 0], '16711680,50,0,0', ['color_brightness' => 50]],
            [['hue' => 0], '16711680,50,70,0', ['color_brightness' => 50, 'brightness' => 70]],
            [['color' => '0xFF0000'], '16711680,50,70,0', ['color_brightness' => 50, 'brightness' => 70]],
            [['rgb' => ['red' => 255, 'green' => 0, 'blue' => 0]], '16711680,100,70,0', ['color_brightness' => 50, 'brightness' => 70]],
            [['color' => '0xAA0000'], '11141120,50,70,0', ['color_brightness' => 50, 'brightness' => 70]],
            [['color' => '0xAA0000'], '11141120,0,70,0', ['brightness' => 70]],
            [['rgb' => ['red' => 170, 'green' => 0, 'blue' => 0]], '16711680,67,70,0', ['color_brightness' => 50, 'brightness' => 70]],
            [['color' => '0xFF0000', 'brightness' => 40], '16711680,50,40,0', ['color_brightness' => 50, 'brightness' => 70]],
            [
                ['rgb' => ['red' => 255, 'green' => 0, 'blue' => 0], 'brightness' => 40],
                '16711680,100,40,0',
                ['color_brightness' => 50, 'brightness' => 70],
            ],
            [['hsv' => ['hue' => 0, 'saturation' => 100, 'value' => 100]], '16711680,100,0,0'],
            [['hsv' => ['hue' => 0, 'saturation' => 100, 'value' => 60]], '16711680,60,0,0'],
            [['hsv' => ['hue' => 0, 'saturation' => 100, 'value' => 60]], '16711680,60,0,0', ['color_brightness' => 50]],
            [['color_brightness' => 40], '16711680,40,0,0', ['color' => '0xFF0000']],
            [['color' => 'random'], 'SET-RAND-RGBW-VALUE:1,1,1,99,0', ['color_brightness' => '99']],
            [['hue' => 'random', 'color_brightness' => 88], 'SET-RAND-RGBW-VALUE:1,1,1,88,0'],
            [['color' => 'random', 'color_brightness' => 98], 'SET-RAND-RGBW-VALUE:1,1,1,98,0', ['color_brightness' => '99']],
            [['hue' => 0, 'color_brightness' => 0, 'turnOnOff' => true], '16711680,0,0,3', [], ChannelFunction::DIMMERANDRGBLIGHTING],
            [['hue' => 0, 'color_brightness' => 0, 'turnOnOff' => 2], '16711680,0,0,2', [], ChannelFunction::DIMMERANDRGBLIGHTING],
            [['hue' => 0, 'color_brightness' => 0, 'turnOnOff' => 1], '16711680,0,0,1', [], ChannelFunction::DIMMERANDRGBLIGHTING],
            [['hue' => 0, 'color_brightness' => 0, 'turnOnOff' => true], '16711680,0,0,1', [], ChannelFunction::DIMMER],
            [['hue' => 0, 'color_brightness' => 0, 'turnOnOff' => 2], '16711680,0,0,1', [], ChannelFunction::DIMMER],
            [['hue' => 0, 'color_brightness' => 0, 'turnOnOff' => 23], '16711680,0,0,1', [], ChannelFunction::DIMMER],
            [['hue' => 0, 'color_brightness' => 0, 'turnOnOff' => true], '16711680,0,0,2', [], ChannelFunction::RGBLIGHTING],
            [['hue' => 0, 'color_brightness' => 0, 'turnOnOff' => 1], '16711680,0,0,2', [], ChannelFunction::RGBLIGHTING],
            [['hue' => 0, 'color_brightness' => 0, 'turnOnOff' => false], '16711680,0,0,0'],
        ];
    }
}
