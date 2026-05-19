<?php

namespace App\Tests\Traits;

use App\Entity\Main\Scene;
use App\Enums\ChannelFunction;
use App\Enums\ChannelFunctionAction;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @method MockObject createMock(string $className)
 */
trait SceneMocks {
    protected function createSceneMock(): Scene {
        $scene = $this->createMock(Scene::class);
        $scene->method('getId')->willReturn(1);
        $scene->method('getFunction')->willReturn(ChannelFunction::SCENE());
        $scene->method('getPossibleActions')->willReturn([ChannelFunctionAction::EXECUTE()]);
        return $scene;
    }
}
