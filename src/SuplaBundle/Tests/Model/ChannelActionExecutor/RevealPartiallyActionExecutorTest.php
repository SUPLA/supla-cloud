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
     * @dataProvider validParamsProvider
     */
    public function testTransformingActionParamsFromApi(array $apiParams, array $databaseParams) {
        $executor = new RevealPartiallyActionExecutor();
        $subject = $this->createMock(ActionableSubject::class);
        $subject->method('getFunction')->willReturn(ChannelFunction::CONTROLLINGTHEFACADEBLIND());
        $transformedParams = $executor->validateAndTransformActionParamsFromApi($subject, $apiParams);
        $this->assertEquals($databaseParams, $transformedParams);
    }

    /**
     * @dataProvider validParamsProvider
     */
    public function testTransformingActionParamsForApi(array $apiParams, array $databaseParams) {
//        if (!$expectValid) {
//            $this->expectException(InvalidArgumentException::class);
//        }
        $executor = new RevealPartiallyActionExecutor();
        $subject = $this->createMock(ActionableSubject::class);
        $subject->method('getFunction')->willReturn(ChannelFunction::CONTROLLINGTHEFACADEBLIND());
        $transformedParams = $executor->transformActionParamsForApi($subject, $databaseParams);
        $this->assertEquals($apiParams, $transformedParams);
    }

    public function validParamsProvider() {
        return [
            [['percentage' => 0], ['percentage' => 0]],
            [['percentage' => 50], ['percentage' => 50]],
            [['percentage' => 100], ['percentage' => 100]],
            [['percentage' => '100'], ['percentage' => 100]],
            [['percentage' => -1], ['percentageDelta' => -1]],
            [['percentage' => '+100'], ['percentageDelta' => 100]],
            [['percentage' => '+0'], ['percentageDelta' => 0]],
            [['percentage' => '+10', 'tilt' => 10], ['percentageDelta' => 10, 'tilt' => 10]],
            [['percentage' => '+10', 'tilt' => '+10'], ['percentageDelta' => 10, 'tiltDelta' => 10]],
            [['percentage' => '+10', 'tilt' => -10], ['percentageDelta' => 10, 'tiltDelta' => -10]],
            [['percentage' => '66', 'tilt' => '+0'], ['percentage' => 66, 'tiltDelta' => 0]],
        ];
    }

    /** @dataProvider invalidParamsProvider */
    public function testInvalidParamsProvider(array $invalidParams) {
        $this->expectException(InvalidArgumentException::class);
        $executor = new RevealPartiallyActionExecutor();
        $subject = $this->createMock(ActionableSubject::class);
        $subject->method('getFunction')->willReturn(ChannelFunction::CONTROLLINGTHEFACADEBLIND());
        $executor->validateAndTransformActionParamsFromApi($subject, $invalidParams);
    }

    public function invalidParamsProvider() {
        return [
            [['percentage' => 101]],
            [['percentage2' => 50]],
            [['percentage' => 50, 'other' => 50]],
            [[]],
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
