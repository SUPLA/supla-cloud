<?php
namespace SuplaBundle\Tests\Model\ChannelActionExecutor;

use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use SuplaBundle\Entity\Main\HasFunction;
use SuplaBundle\Model\ChannelActionExecutor\TurnOnActionExecutor;

class TurnOnActionExecutorTest extends PHPUnit_Framework_TestCase {
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
        return [
            [[], true],
            [['alexaCorrelationToken' => 'abcd'], true],
            [['googleRequestId' => 'abcd'], true],
        ];
    }
}
