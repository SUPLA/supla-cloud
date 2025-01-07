<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace SuplaBundle\Tests\Model\UserConfigTranslator;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Model\UserConfigTranslator\ActionTriggerParamsTranslator;
use SuplaBundle\Repository\ActionableSubjectRepository;
use SuplaBundle\Serialization\RequestFiller\SubjectActionFiller;
use SuplaBundle\Tests\Integration\Traits\UnitTestHelper;
use SuplaBundle\Tests\Traits\ChannelStub;
use SuplaBundle\Tests\Traits\SceneMocks;
use SuplaBundle\Tests\Traits\UserMocks;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ActionTriggerParamsTranslatorTest extends TestCase {
    use UnitTestHelper;
    use UserMocks;
    use SceneMocks;

    /** @var ActionTriggerParamsTranslator */
    private $configTranslator;
    /** @var ActionableSubjectRepository|MockObject */
    private $subjectActionFiler;
    /** @var ChannelActionExecutor|MockObject */
    private $actionExecutorMock;

    /** @before */
    public function createTranslator() {
        $this->subjectActionFiler = $this->createMock(SubjectActionFiller::class);
        $this->actionExecutorMock = $this->createMock(ChannelActionExecutor::class);
        $this->configTranslator = new ActionTriggerParamsTranslator(
            $this->createMock(EntityManagerInterface::class),
            $this->subjectActionFiler,
            $this->actionExecutorMock,
            $this->createMock(ActionableSubjectRepository::class),
        );
        $this->configTranslator->setTokenStorage($this->getMockedTokenStorage());
    }

    public function testGettingSupportedTriggers() {
        $channel = ChannelStub::create(ChannelFunction::ACTION_TRIGGER())
            ->properties(['actionTriggerCapabilities' => ['HOLD', 'PRESS_3X']]);
        $config = $this->configTranslator->getConfig($channel);
        $this->assertArrayHasKey('actionTriggerCapabilities', $config);
        $this->assertEquals(['HOLD', 'PRESS_3X'], $config['actionTriggerCapabilities']);
    }

    public function testCloudCannotChangeSupportedTriggers() {
        $channel = ChannelStub::create(ChannelFunction::ACTION_TRIGGER())
            ->properties(['actionTriggerCapabilities' => ['HOLD', 'PRESS_3X']]);
        $this->configTranslator->setConfig($channel, ['actionTriggerCapabilities' => ['PRESS_2X']]);
        $config = $this->configTranslator->getConfig($channel);
        $this->assertArrayHasKey('actionTriggerCapabilities', $config);
        $this->assertEquals(['HOLD', 'PRESS_3X'], $config['actionTriggerCapabilities']);
    }

    public function testCanSetEmptyActions() {
        $channel = ChannelStub::create(ChannelFunction::ACTION_TRIGGER());
        $this->configTranslator->setConfig($channel, ['actions' => null]);
        $config = $this->configTranslator->getConfig($channel);
        $this->assertArrayHasKey('actions', $config);
        $this->assertEmpty($config['actions']);
    }

    public function testCanSetValidActions() {
        $actions = [
            'PRESS' => ['subjectId' => 1, 'subjectType' => 'channel', 'action' => ['id' => ChannelFunctionAction::OPEN, 'param' => []]],
            'HOLD' => ['subjectId' => 1, 'subjectType' => 'scene', 'action' => ['id' => ChannelFunctionAction::EXECUTE, 'param' => []]],
        ];
        $channel = ChannelStub::create(ChannelFunction::ACTION_TRIGGER(), $this)
            ->properties(['actionTriggerCapabilities' => ['HOLD', 'PRESS', 'PRESS_2X']]);
        $this->subjectActionFiler->method('getSubjectAndAction')
            ->willReturnOnConsecutiveCalls(
                [ChannelStub::create(ChannelFunction::CONTROLLINGTHEDOORLOCK()), ChannelFunctionAction::OPEN(), []],
                [$this->createSceneMock(), ChannelFunctionAction::EXECUTE(), []]
            );
        $this->configTranslator->setConfig($channel, ['actions' => $actions]);
        $this->assertEquals($actions, $channel->getUserConfig()['actions']);
        return $channel;
    }

    /** @depends testCanSetValidActions */
    public function testGettingActionsInConfig(IODeviceChannel $channel) {
        $config = $this->configTranslator->getConfig($channel);
        $this->assertArrayHasKey('actions', $config);
        $this->assertCount(2, $config['actions']);
    }

    public function testFillsActionParamsIfMissing() {
        $actions = [
            'PRESS' => ['subjectId' => 1, 'subjectType' => 'channel', 'action' => ['id' => ChannelFunctionAction::OPEN]],
            'PRESS_2X' => ['subjectId' => 1, 'subjectType' => 'scene', 'action' => ['id' => ChannelFunctionAction::EXECUTE]],
        ];
        $channel = ChannelStub::create(ChannelType::ACTION_TRIGGER(), $this)
            ->properties(['actionTriggerCapabilities' => ['PRESS_2X', 'PRESS']]);
        $this->subjectActionFiler->method('getSubjectAndAction')
            ->willReturnOnConsecutiveCalls(
                [ChannelStub::create(ChannelFunction::CONTROLLINGTHEDOORLOCK()), ChannelFunctionAction::OPEN(), []],
                [$this->createSceneMock(), ChannelFunctionAction::EXECUTE(), []]
            );
        $this->configTranslator->setConfig($channel, ['actions' => $actions]);
        $actions['PRESS']['action']['param'] = [];
        $actions['PRESS_2X']['action']['param'] = [];
        $this->assertEquals($actions, $channel->getUserConfig()['actions']);
    }

    public function testCanSetAtForwardAction() {
        $actions = ['PRESS' => [
            'subjectType' => ActionableSubjectType::OTHER,
            'action' => ['id' => ChannelFunctionAction::AT_FORWARD_OUTSIDE],
        ]];
        $channel = ChannelStub::create(ChannelType::ACTION_TRIGGER())->properties(['actionTriggerCapabilities' => ['PRESS']]);
        $this->configTranslator->setConfig($channel, ['actions' => $actions]);
        $this->assertEquals($actions, $channel->getUserConfig()['actions']);
    }

    public function testCanSetAtDisableAction() {
        $actions = ['PRESS' => [
            'subjectType' => ActionableSubjectType::OTHER,
            'action' => ['id' => ChannelFunctionAction::AT_DISABLE_LOCAL_FUNCTION],
        ]];
        $channel = ChannelStub::create(ChannelType::ACTION_TRIGGER())->properties(['actionTriggerCapabilities' => ['PRESS']]);
        $this->configTranslator->setConfig($channel, ['actions' => $actions]);
        $this->assertEquals($actions, $channel->getUserConfig()['actions']);
    }

    public function testCanSetCopyChannelState() {
        $actions = ['PRESS' => [
            'subjectType' => ActionableSubjectType::CHANNEL,
            'subjectId' => 1,
            'action' => [
                'id' => ChannelFunctionAction::COPY,
                'param' => ['sourceChannelId' => 2],
            ],
        ]];
        $channel = ChannelStub::create(ChannelType::ACTION_TRIGGER(), $this)->properties(['actionTriggerCapabilities' => ['PRESS']]);
        $this->subjectActionFiler->method('getSubjectAndAction')
            ->willReturnOnConsecutiveCalls(
                [ChannelStub::create(ChannelFunction::POWERSWITCH()), ChannelFunctionAction::COPY(), ['SOURCE_CHANNEL_ID' => 2]],
            );
        $this->actionExecutorMock->method('transformActionParamsForApi')->willReturnOnConsecutiveCalls(['sourceChannelId' => 2]);
        $this->configTranslator->setConfig($channel, ['actions' => $actions]);
        $this->assertEquals($actions, $this->configTranslator->getConfig($channel)['actions']->toArray());
        $actions['PRESS']['action']['param'] = ['SOURCE_CHANNEL_ID' => 2];
        $this->assertEquals($actions, $channel->getUserConfig()['actions']);
    }

    public function testCannotSetActionWithNoSubjectType() {
        $this->expectException(\InvalidArgumentException::class);
        $actions = ['PRESS' => ['subjectId' => 1, 'action' => ['id' => ChannelFunctionAction::OPEN]]];
        $channel = ChannelStub::create(ChannelType::ACTION_TRIGGER())->properties(['actionTriggerCapabilities' => ['PRESS']]);
        $this->configTranslator->setConfig($channel, ['actions' => $actions]);
    }

    public function testCannotSetActionWithNoAction() {
        $this->expectException(\InvalidArgumentException::class);
        $actions = ['PRESS' => ['subjectType' => 'channel', 'subjectId' => 1]];
        $channel = ChannelStub::create(ChannelType::ACTION_TRIGGER())->properties(['actionTriggerCapabilities' => ['PRESS']]);
        $this->configTranslator->setConfig($channel, ['actions' => $actions]);
    }

    public function testCannotSetActionWithInvalidSubject() {
        $this->expectException(NotFoundHttpException::class);
        $actions = [
            'PRESS' => ['subjectId' => 1, 'subjectType' => 'channel', 'action' => ['id' => ChannelFunctionAction::OPEN]],
            'PRESS_2X' => ['subjectId' => 1, 'subjectType' => 'scene', 'action' => ['id' => ChannelFunctionAction::EXECUTE]],
        ];
        $channel = ChannelStub::create(ChannelType::ACTION_TRIGGER(), $this)
            ->properties(['actionTriggerCapabilities' => ['PRESS', 'PRESS_2X']]);
        $this->subjectActionFiler->method('getSubjectAndAction')->willThrowException(new NotFoundHttpException());
        $this->configTranslator->setConfig($channel, ['actions' => $actions]);
    }

    public function testCanNotSetInvalidActionParams() {
        $this->expectException(\InvalidArgumentException::class);
        $actions = [
            'PRESS' => ['subjectId' => 1, 'subjectType' => 'channel', 'action' => ['id' => ChannelFunctionAction::OPEN, 'params' => []]],
        ];
        $this->subjectActionFiler->method('getSubjectAndAction')->willThrowException(new \InvalidArgumentException());
        $channel = ChannelStub::create(ChannelType::ACTION_TRIGGER(), $this)
            ->properties(['actionTriggerCapabilities' => ['PRESS', 'PRESS_2X']]);
        $this->configTranslator->setConfig($channel, ['actions' => $actions]);
    }

    public function testCannotSetUnsupportedTrigger() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('HOLD trigger is not');
        $actions = [
            'HOLD' => ['subjectId' => 1, 'subjectType' => 'channel', 'action' => ['id' => ChannelFunctionAction::OPEN, 'params' => []]],
        ];
        $channel = ChannelStub::create(ChannelType::ACTION_TRIGGER())
            ->properties(['actionTriggerCapabilities' => ['PRESS', 'PRESS_2X']]);
        $this->subjectActionFiler->method('getSubjectAndAction')
            ->willReturnOnConsecutiveCalls(
                [ChannelStub::create(ChannelFunction::CONTROLLINGTHEDOORLOCK()), ChannelFunctionAction::OPEN(), []],
                [$this->createSceneMock(), ChannelFunctionAction::EXECUTE(), []]
            );
        $this->configTranslator->setConfig($channel, ['actions' => $actions]);
    }

    public function testLowercaseTriggerNamesAreNotSupported() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('press trigger is not');
        $actions = [
            'press' => ['subjectId' => 1, 'subjectType' => 'channel', 'action' => ['id' => ChannelFunctionAction::OPEN, 'param' => []]],
        ];
        $channel = ChannelStub::create(ChannelType::ACTION_TRIGGER())
            ->properties(['actionTriggerCapabilities' => ['PRESS', 'PRESS_2X']]);
        $this->subjectActionFiler->method('getSubjectAndAction')
            ->willReturnOnConsecutiveCalls(
                [ChannelStub::create(ChannelFunction::CONTROLLINGTHEDOORLOCK()), ChannelFunctionAction::OPEN(), []],
                [$this->createSceneMock(), ChannelFunctionAction::EXECUTE(), []]
            );
        $this->configTranslator->setConfig($channel, ['actions' => $actions]);
    }

    public function testCannotSaveRubbishInConfig() {
        $actions = [
            'PRESS' => ['subjectId' => 1, 'subjectType' => 'channel', 'unicorn' => 'flower',
                'action' => ['id' => ChannelFunctionAction::OPEN, 'param' => [], 'unicorn' => 'flower']],
        ];
        $channel = ChannelStub::create(ChannelType::ACTION_TRIGGER(), $this)
            ->properties(['actionTriggerCapabilities' => ['PRESS', 'PRESS_2X']]);
        $this->subjectActionFiler->method('getSubjectAndAction')
            ->willReturnOnConsecutiveCalls(
                [ChannelStub::create(ChannelFunction::CONTROLLINGTHEDOORLOCK()), ChannelFunctionAction::OPEN(), []],
                [$this->createSceneMock(), ChannelFunctionAction::EXECUTE(), []]
            );
        $this->configTranslator->setConfig($channel, ['actions' => $actions]);
        unset($actions['PRESS']['unicorn']);
        unset($actions['PRESS']['action']['unicorn']);
        $this->assertEquals($actions, $channel->getUserConfig()['actions']);
    }

    public function testGettingRelatedChannelIdNull() {
        $channel = new IODeviceChannel();
        $config = $this->configTranslator->getConfig($channel);
        $this->assertArrayHasKey('relatedChannelId', $config);
        $this->assertArrayHasKey('hideInChannelsList', $config);
        $this->assertNull($config['relatedChannelId']);
        $this->assertFalse($config['hideInChannelsList']);
    }

    public function testGettingRelatedChannelId() {
        $channel = new IODeviceChannel();
        $channel->setParam1(123);
        $config = $this->configTranslator->getConfig($channel);
        $this->assertArrayHasKey('relatedChannelId', $config);
        $this->assertArrayHasKey('hideInChannelsList', $config);
        $this->assertEquals(123, $config['relatedChannelId']);
        $this->assertTrue($config['hideInChannelsList']);
    }

    public function testCloudCannotChangeRelatedChannelId() {
        $channel = new IODeviceChannel();
        $channel->setParam1(123);
        $this->configTranslator->setConfig($channel, ['relatedChannelId' => 234]);
        $config = $this->configTranslator->getConfig($channel);
        $this->assertArrayHasKey('relatedChannelId', $config);
        $this->assertEquals(123, $config['relatedChannelId']);
    }
}
