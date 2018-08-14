<?php
namespace SuplaBundle\Tests\Model\ChannelActionExecutor;

use InvalidArgumentException;
use SuplaBundle\Entity\HasFunction;
use SuplaBundle\Model\ChannelActionExecutor\SetRgbwParametersActionExecutor;

class SetRgbwParametersActionExecutorTest extends \PHPUnit_Framework_TestCase {
    /**
     * @dataProvider validatingActionParamsProvider
     */
    public function testValidatingActionParams($actionParams, bool $expectValid) {
        if (!$expectValid) {
            $this->expectException(InvalidArgumentException::class);
        }
        $executor = new SetRgbwParametersActionExecutor();
        $executor->validateActionParams($this->createMock(HasFunction::class), $actionParams);
    }

    public function validatingActionParamsProvider() {
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
            [['hue' => 0], false],
            [['color' => 1], false],
            [['hue' => 'black', 'color_brightness' => 100], false],
            [['brightness' => -1], false],
            [['brightness' => 101], false],
            [['brightness' => 'ala'], false],
        ];
    }

    public function testConvertingStringColorToInt() {
        $executor = new SetRgbwParametersActionExecutor();
        $subject = $this->createMock(HasFunction::class);
        $validated = $executor->validateActionParams($subject, ['color' => '12', 'color_brightness' => '56']);
        $this->assertSame(12, $validated['color']);
        $this->assertSame(56, $validated['color_brightness']);
    }

    public function testConvertingHexColorToInt() {
        $executor = new SetRgbwParametersActionExecutor();
        $subject = $this->createMock(HasFunction::class);
        $validated = $executor->validateActionParams($subject, ['color' => '0xFFCC77', 'color_brightness' => '56']);
        $this->assertSame(0xFFCC77, $validated['color']);
    }
}
