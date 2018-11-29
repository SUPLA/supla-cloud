<?php
namespace SuplaBundle\Tests\Model\ChannelActionExecutor;

use InvalidArgumentException;
use SuplaBundle\Entity\HasFunction;
use SuplaBundle\Model\ChannelActionExecutor\TurnOnActionExecutor;

class TurnOnActionExecutorTest extends \PHPUnit_Framework_TestCase {
    /**
     * @dataProvider validatingActionParamsProvider
     */
    public function testValidatingActionParams($actionParams, bool $expectValid) {
        if (!$expectValid) {
            $this->expectException(InvalidArgumentException::class);
        }
        $executor = new TurnOnActionExecutor();
        $executor->validateActionParams($this->createMock(HasFunction::class), $actionParams);
    }

    public function validatingActionParamsProvider() {
        return [];
    }

    public function testEmptyActionParamArray() {
        $executor = new TurnOnActionExecutor();
        $subject = $this->createMock(HasFunction::class);
        $validated = $executor->validateActionParams($subject, []);
        $this->assertEmpty($validated);
    }

    public function testValidatingActionParamsWithCorrelationToken() {
        $executor = new TurnOnActionExecutor();
        $subject = $this->createMock(HasFunction::class);
        $validated = $executor->validateActionParams($subject, ['alexaCorrelationToken' => 'abcd']);
        $this->assertSame("abcd", $validated['alexaCorrelationToken']);
    }
}
