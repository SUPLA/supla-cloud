<?php
namespace SuplaBundle\Tests\Model\ChannelActionExecutor;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\ChannelActionExecutor\SetDigiglassParametersActionExecutor;
use SuplaBundle\Supla\SuplaServer;
use SuplaBundle\Tests\Integration\Traits\UnitTestHelper;
use SuplaBundle\Tests\Traits\ChannelStub;

class SetDigiglassParametersActionExecutorTest extends TestCase {
    use UnitTestHelper;

    /**
     * @dataProvider expectedServerCommandsProvider
     */
    public function testExpectedServerCommands($actionParams, string $expectCommand) {
        $executor = new SetDigiglassParametersActionExecutor();
        $server = $this->createMock(SuplaServer::class);
        $executor->setSuplaServer($server);
        $server->expects($this->once())->method('executeCommand')->with($expectCommand);
        $channel = new ChannelStub(ChannelFunction::DIGIGLASS_VERTICAL(), $this);
        $channel->id(111);
        $actionParams = $executor->validateAndTransformActionParamsFromApi($channel, $actionParams);
        $executor->execute($channel, $actionParams);
    }

    public static function expectedServerCommandsProvider() {
        return [
            [['transparent' => [0]], 'SET-DIGIGLASS-VALUE:222,333,111,1,1'],
            [['transparent' => 0], 'SET-DIGIGLASS-VALUE:222,333,111,1,1'],
            [['transparent' => '0'], 'SET-DIGIGLASS-VALUE:222,333,111,1,1'],
            [['transparent' => '0,1'], 'SET-DIGIGLASS-VALUE:222,333,111,3,3'],
            [['transparent' => [1]], 'SET-DIGIGLASS-VALUE:222,333,111,2,2'],
            [['opaque' => [0]], 'SET-DIGIGLASS-VALUE:222,333,111,1,0'],
            [['transparent' => [0], 'opaque' => [1]], 'SET-DIGIGLASS-VALUE:222,333,111,3,1'],
            [['transparent' => [1], 'opaque' => [1]], 'SET-DIGIGLASS-VALUE:222,333,111,2,0'],
            [['transparent' => [0, 2], 'opaque' => [1, 3]], 'SET-DIGIGLASS-VALUE:222,333,111,15,5'],
            [['transparent' => '0, 2', 'opaque' => '1,3'], 'SET-DIGIGLASS-VALUE:222,333,111,15,5'],
            [['transparent' => [5], 'opaque' => [1, 2]], 'SET-DIGIGLASS-VALUE:222,333,111,38,32'],
            [['mask' => 0], 'SET-DIGIGLASS-VALUE:222,333,111,127,0'],
            [['mask' => 16, 'transparent' => 1], 'SET-DIGIGLASS-VALUE:222,333,111,127,16'],
        ];
    }

    /** @dataProvider actionParametersProvider */
    public function testValidatingActionParams(array $actionParams, $expectValid = true) {
        if (!$expectValid) {
            $this->expectException(InvalidArgumentException::class);
        }
        $executor = new SetDigiglassParametersActionExecutor();
        $channel = $this->createMock(IODeviceChannel::class);
        $channel->method('getType')->willReturn(ChannelType::DIGIGLASS());
        $executor->validateAndTransformActionParamsFromApi($channel, $actionParams);
        $this->assertTrue(true);
    }

    public static function actionParametersProvider() {
        return array_merge([
            [[], false],
            [['transparent' => [], 'opaque' => []], false],
            [['transparent' => [], 'opaque' => [], 'unicorn' => []], false],
            [['unicorn' => []], false],
        ], self::expectedServerCommandsProvider());
    }
}
