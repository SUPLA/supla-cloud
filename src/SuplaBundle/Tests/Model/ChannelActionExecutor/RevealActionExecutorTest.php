<?php
namespace SuplaBundle\Tests\Model\ChannelActionExecutor;

use InvalidArgumentException;
use SuplaBundle\Entity\HasFunction;
use SuplaBundle\Model\ChannelActionExecutor\RevealActionExecutor;

class RevealActionExecutorTest extends \PHPUnit_Framework_TestCase {
    /**
     * @dataProvider validatingActionParamsProvider
     */
    public function testValidatingActionParams($actionParams, bool $expectValid) {
        if (!$expectValid) {
            $this->expectException(InvalidArgumentException::class);
        }
        $executor = new RevealActionExecutor();
        $executor->validateActionParams($this->createMock(HasFunction::class), $actionParams);
    }

    public function validatingActionParamsProvider() {
        return [
            [['percentage' => 0], true],
            [['percentage' => 50], true],
            [['percentage' => 100], true],
            [['percentage' => '100'], true],
            [['percentage' => -1], false],
            [['percentage' => 101], false],
            [['percentage2' => 50], false],
            [['percentage' => 50, 'other' => 50], false],
            [[], true],
        ];
    }

    public function testConvertingStringPercentageToInt() {
        $executor = new RevealActionExecutor();
        $subject = $this->createMock(HasFunction::class);
        $validated = $executor->validateActionParams($subject, ['percentage' => '56']);
        $this->assertSame(56, $validated['percentage']);
    }

    public function testConvertingPercentToPercentage() {
        $executor = new RevealActionExecutor();
        $subject = $this->createMock(HasFunction::class);
        $validated = $executor->validateActionParams($subject, ['percent' => 99]);
        $this->assertSame(99, $validated['percentage']);
    }

    public function testValidatingActionParamsWithCorrelationToken() {
        $executor = new RevealActionExecutor();
        $subject = $this->createMock(HasFunction::class);
        $validated = $executor->validateActionParams($subject, ['percentage' => '55', 'alexaCorrelationToken' => 'abcd']);
        $this->assertSame(55, $validated['percentage']);
        $this->assertSame("abcd", $validated['alexaCorrelationToken']);
    }
}
