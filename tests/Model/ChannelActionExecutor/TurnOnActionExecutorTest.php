<?php
namespace App\Tests\Model\ChannelActionExecutor;

use App\Entity\ActionableSubject;
use App\Model\ChannelActionExecutor\TurnOnActionExecutor;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

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
