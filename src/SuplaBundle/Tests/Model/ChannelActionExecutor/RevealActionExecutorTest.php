<?php
namespace SuplaBundle\Tests\Model\ChannelActionExecutor;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\HasFunction;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Model\ChannelActionExecutor\RevealActionExecutor;
use SuplaBundle\Supla\SuplaServer;

class RevealActionExecutorTest extends TestCase {
    /**
     * @dataProvider validatingActionParamsProvider
     */
    public function testValidatingActionParams($actionParams, bool $expectValid) {
        if (!$expectValid) {
            $this->expectException(InvalidArgumentException::class);
        }
        $executor = new RevealActionExecutor();
        $validParams = $executor->validateActionParams($this->createMock(HasFunction::class), $actionParams);
        $this->assertNotNull($validParams);
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

    public function testValidatingActionParamsWithGoogleRequestId() {
        $executor = new RevealActionExecutor();
        $subject = $this->createMock(HasFunction::class);
        $validated = $executor->validateActionParams($subject, ['percentage' => '55', 'googleRequestId' => 'abcd']);
        $this->assertSame(55, $validated['percentage']);
        $this->assertSame("abcd", $validated['googleRequestId']);
    }

    /**
     * @dataProvider expectedServerCommandsProvider
     */
    public function testExpectedServerCommands($actionParams, string $expectCommand) {
        $executor = new RevealActionExecutor();
        $server = $this->createMock(SuplaServer::class);
        $executor->setSuplaServer($server);
        $server->expects($this->once())->method('executeSetCommand')->with($expectCommand);
        $channel = $this->createMock(IODeviceChannel::class);
        $channel->method('buildServerSetCommand')->willReturnCallback(function ($type, $actionParams) {
            return 'SET-CHAR-VALUE:1,2,3,' . $actionParams[0];
        });
        $executor->execute($channel, $actionParams);
    }

    public function expectedServerCommandsProvider() {
        return [
            [['percentage' => 0], 'SET-CHAR-VALUE:1,2,3,110'],
            [['percentage' => 50], 'SET-CHAR-VALUE:1,2,3,60'],
            [['percentage' => 60], 'SET-CHAR-VALUE:1,2,3,50'],
            [['percentage' => 100], 'SET-CHAR-VALUE:1,2,3,10'],
            [[], 'SET-CHAR-VALUE:1,2,3,10'],
        ];
    }
}
