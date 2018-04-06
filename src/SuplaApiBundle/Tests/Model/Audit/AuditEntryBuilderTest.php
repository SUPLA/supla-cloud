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

namespace SuplaApiBundle\Tests\Model\Audit;

use Doctrine\ORM\EntityManagerInterface;
use SuplaApiBundle\Model\Audit\AuditEntryBuilder;
use SuplaBundle\Controller\ClientAppController;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\AuditedAction;
use SuplaBundle\Enums\ChannelFunction;

class AuditEntryBuilderTest extends \PHPUnit_Framework_TestCase {
    /** @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $entityManager;
    /** @var AuditEntryBuilder */
    private $builder;

    /** @before */
    public function init() {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->builder = new AuditEntryBuilder($this->entityManager);
    }

    public function testBuildingWithoutAction() {
        $this->expectException(\InvalidArgumentException::class);
        $this->builder->build();
    }

    public function testBuildingSimpleEntry() {
        $entry = $this->builder->setAction(AuditedAction::AUTHENTICATION())->build();
        $this->assertEquals(AuditedAction::AUTHENTICATION(), $entry->getAction());
        $this->assertNull($entry->getUser());
        $this->assertTrue($entry->isSuccessful());
    }

    public function testBuildingEntryWithUser() {
        $user = $this->createMock(User::class);
        $entry = $this->builder->setAction(AuditedAction::AUTHENTICATION())->setUser($user)->build();
        $this->assertEquals($user, $entry->getUser());
    }

    public function testUnsuccessful() {
        $entry = $this->builder->setAction(AuditedAction::AUTHENTICATION())->unsuccessful()->build();
        $this->assertFalse($entry->isSuccessful());
    }

    public function testSettingTextParam() {
        $entry = $this->builder->setAction(AuditedAction::PASSWORD_RESET())->setTextParam('zamel')->build();
        $this->assertEquals('zamel', $entry->getTextParam());
    }

    public function testSettingIntParam() {
        $entry = $this->builder->setAction(AuditedAction::PASSWORD_RESET())->setIntParam(42)->build();
        $this->assertEquals(42, $entry->getIntParam());
    }

    public function testSettingIntParamWithString() {
        $entry = $this->builder->setAction(AuditedAction::PASSWORD_RESET())->setIntParam('42')->build();
        $this->assertEquals(42, $entry->getIntParam());
    }

    public function testSettingIntParamWithEnum() {
        $entry = $this->builder
            ->setAction(AuditedAction::PASSWORD_RESET())
            ->setIntParam(ChannelFunction::CONTROLLINGTHEGARAGEDOOR())
            ->build();
        $this->assertEquals(ChannelFunction::CONTROLLINGTHEGARAGEDOOR, $entry->getIntParam());
    }

    public function testSettingIntParamWithInvalidNumber() {
        $this->expectException(\InvalidArgumentException::class);
        $this->builder->setAction(AuditedAction::PASSWORD_RESET())->setIntParam('zamel')->build();
    }
}
