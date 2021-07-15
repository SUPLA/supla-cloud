<?php
namespace SuplaBundle\Tests\Model\ChannelActionExecutor;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\HasFunction;
use SuplaBundle\Model\ChannelActionExecutor\TurnOnActionExecutor;

class TurnOnActionExecutorTest extends TestCase {
    /**
     * @dataProvider validatingActionParamsProvider
     */
    public function testValidatingActionParams($actionParams, bool $expectValid) {
        if (!$expectValid) {
            $this->expectException(InvalidArgumentException::class);
        }
        $executor = new TurnOnActionExecutor();
        $params = $executor->validateActionParams($this->createMock(HasFunction::class), $actionParams);
        $this->assertNotNull($params);
    }

    public function validatingActionParamsProvider() {
        return [
            [[], true],
            [['alexaCorrelationToken' => 'abcd'], true],
            [['googleRequestId' => 'abcd'], true],
        ];
    }
}
