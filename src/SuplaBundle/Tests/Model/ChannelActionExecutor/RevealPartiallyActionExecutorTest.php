<?php
namespace SuplaBundle\Tests\Model\ChannelActionExecutor;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Model\ChannelActionExecutor\RevealPartiallyActionExecutor;
use SuplaBundle\Supla\SuplaServer;
use SuplaBundle\Tests\Integration\Traits\UnitTestHelper;
use SuplaBundle\Tests\Traits\ChannelStub;

class RevealPartiallyActionExecutorTest extends TestCase {
    use UnitTestHelper;

    /**
     * @dataProvider validatingActionParamsProvider
     */
    public function testValidatingActionParams($actionParams, bool $expectValid) {
        if (!$expectValid) {
            $this->expectException(InvalidArgumentException::class);
        }
        $executor = new RevealPartiallyActionExecutor();
        $subject = $this->createMock(ActionableSubject::class);
        $subject->method('getFunction')->willReturn(ChannelFunction::CONTROLLINGTHEROLLERSHUTTER());
        $validParams = $executor->validateAndTransformActionParamsFromApi($subject, $actionParams);
        $this->assertNotNull($validParams);
    }

    public function validatingActionParamsProvider() {
        return [
            [['percentage' => 0], true],
            [['percentage' => 50], true],
            [['percentage' => 100], true],
            [['percentage' => '100'], true],
            [['percentage' => -1], true],
            [['percentage' => '+100'], true],
            [['percentage' => 101], false],
            [['percentage2' => 50], false],
            [['percentage' => 50, 'other' => 50], false],
            [[], false],
        ];
    }

    public function testConvertingStringPercentageToInt() {
        $executor = new RevealPartiallyActionExecutor();
        $subject = $this->createMock(ActionableSubject::class);
        $subject->method('getFunction')->willReturn(ChannelFunction::CONTROLLINGTHEROLLERSHUTTER());
        $validated = $executor->validateAndTransformActionParamsFromApi($subject, ['percentage' => '56']);
        $this->assertSame(56, $validated['percentage']);
    }

    public function testConvertingPercentToPercentage() {
        $executor = new RevealPartiallyActionExecutor();
        $subject = $this->createMock(ActionableSubject::class);
        $subject->method('getFunction')->willReturn(ChannelFunction::CONTROLLINGTHEROLLERSHUTTER());
        $validated = $executor->validateAndTransformActionParamsFromApi($subject, ['percent' => 99]);
        $this->assertSame(99, $validated['percentage']);
    }

    /**
     * @dataProvider expectedServerCommandsProvider
     */
    public function testExpectedServerCommands($actionParams, string $expectCommand) {
        $executor = new RevealPartiallyActionExecutor();
        $server = $this->createMock(SuplaServer::class);
        $executor->setSuplaServer($server);
        $server->expects($this->once())->method('executeCommand')->with($expectCommand);
        $channel = ChannelStub::create(ChannelFunction::CONTROLLINGTHEROLLERSHUTTER(), $this);
        $params = $executor->validateAndTransformActionParamsFromApi($channel, $actionParams);
        $executor->execute($channel, $params);
    }

    public function expectedServerCommandsProvider() {
        return [
            [['percentage' => 0], 'ACTION-SHUT-PARTIALLY:222,333,1,100,0,-1,0'],
            [['percentage' => 50], 'ACTION-SHUT-PARTIALLY:222,333,1,50,0,-1,0'],
            [['percentage' => 60], 'ACTION-SHUT-PARTIALLY:222,333,1,40,0,-1,0'],
            [['percentage' => 100], 'ACTION-SHUT-PARTIALLY:222,333,1,0,0,-1,0'],
            [['percentage' => '+40'], 'ACTION-SHUT-PARTIALLY:222,333,1,-40,1,-1,0'],
            [['percentage' => '-40'], 'ACTION-SHUT-PARTIALLY:222,333,1,40,1,-1,0'],
        ];
    }
}
