<?php
namespace App\Tests\Model\ChannelActionExecutor;

use App\Entity\EntityUtils;
use App\Entity\Main\IODeviceChannelGroup;
use App\Entity\Main\User;
use App\Enums\ChannelFunction;
use App\Enums\ChannelFunctionAction;
use App\Model\ChannelActionExecutor\SwitchToManualModeActionExecutor;
use App\Model\ChannelActionExecutor\SwitchToProgramModeActionExecutor;
use App\Supla\SuplaServer;
use App\Tests\Integration\Traits\UnitTestHelper;
use App\Tests\Traits\ChannelStub;
use PHPUnit\Framework\TestCase;

class SwitchModeActionExecutorTest extends TestCase {
    use UnitTestHelper;

    /** @dataProvider modeProvider */
    public function testChannelAndGroupCommands(string $executorClass, string $actionName, int $actionId, string $mode) {
        $executor = new $executorClass();
        $this->assertSame($actionId, $executor->getSupportedAction()->getId());
        $this->assertEquals($executor->getSupportedAction(), ChannelFunctionAction::fromString($actionName));

        $channel = new ChannelStub(ChannelFunction::HVAC_THERMOSTAT(), $this);
        $channel->id(111);
        $user = $this->createEntityMock(User::class, 222);
        $group = new IODeviceChannelGroup($user);
        EntityUtils::setField($group, 'id', 444);

        $server = $this->createMock(SuplaServer::class);
        $call = 0;
        $server->expects($this->exactly(2))->method('executeCommand')->willReturnCallback(
            function (string $command) use ($mode, &$call): string {
                $expected = ["ACTION-SWITCH-TO-$mode-MODE:222,333,111", "ACTION-CG-SWITCH-TO-$mode-MODE:222,444"];
                $this->assertSame($expected[$call++], $command);
                return '';
            }
        );
        $executor->setSuplaServer($server);
        $executor->execute($channel);
        $executor->execute($group);
    }

    public static function modeProvider(): array {
        return [
            [SwitchToProgramModeActionExecutor::class, 'HVAC_SWITCH_TO_PROGRAM_MODE', 231, 'PROGRAM'],
            [SwitchToManualModeActionExecutor::class, 'HVAC_SWITCH_TO_MANUAL_MODE', 232, 'MANUAL'],
        ];
    }
}
