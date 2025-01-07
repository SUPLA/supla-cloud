<?php
namespace SuplaBundle\Tests\Model\ChannelActionExecutor;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\ActionableSubject;
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
        $params = $executor->validateAndTransformActionParamsFromApi($this->createMock(ActionableSubject::class), $actionParams);
        $this->assertNotNull($params);
    }

    public static function validatingActionParamsProvider() {
        return [
            [[], true],
            [['alexaCorrelationToken' => 'abcd'], false],
            [['googleRequestId' => 'abcd'], false],
        ];
    }
}
