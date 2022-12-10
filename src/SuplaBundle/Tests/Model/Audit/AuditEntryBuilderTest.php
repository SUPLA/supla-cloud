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

namespace SuplaBundle\Tests\Model\Audit;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\AuditedEvent;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Model\Audit\AuditEntryBuilder;

class AuditEntryBuilderTest extends TestCase {
    /** @var EntityManagerInterface|MockObject */
    private $entityManager;
    /** @var AuditEntryBuilder */
    private $builder;

    /** @before */
    public function init() {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->builder = new AuditEntryBuilder($this->entityManager);
    }

    public function testBuildingWithoutEvent() {
        $this->expectException(\InvalidArgumentException::class);
        $this->builder->build();
    }

    public function testBuildingSimpleEntry() {
        $entry = $this->builder->setEvent(AuditedEvent::AUTHENTICATION_SUCCESS())->build();
        $this->assertEquals(AuditedEvent::AUTHENTICATION_SUCCESS(), $entry->getEvent());
        $this->assertNull($entry->getUser());
    }

    public function testBuildingEntryWithUser() {
        $user = $this->createMock(User::class);
        $entry = $this->builder->setEvent(AuditedEvent::AUTHENTICATION_SUCCESS())->setUser($user)->build();
        $this->assertEquals($user, $entry->getUser());
    }

    public function testSettingTextParam() {
        $entry = $this->builder->setEvent(AuditedEvent::PASSWORD_RESET())->setTextParam('zamel')->build();
        $this->assertEquals('zamel', $entry->getTextParam());
    }

    public function testSettingIntParam() {
        $entry = $this->builder->setEvent(AuditedEvent::PASSWORD_RESET())->setIntParam(42)->build();
        $this->assertEquals(42, $entry->getIntParam());
    }

    public function testSettingIntParamWithString() {
        $entry = $this->builder->setEvent(AuditedEvent::PASSWORD_RESET())->setIntParam('42')->build();
        $this->assertEquals(42, $entry->getIntParam());
    }

    public function testSettingIntParamWithEnum() {
        $entry = $this->builder
            ->setEvent(AuditedEvent::PASSWORD_RESET())
            ->setIntParam(ChannelFunction::CONTROLLINGTHEGARAGEDOOR())
            ->build();
        $this->assertEquals(ChannelFunction::CONTROLLINGTHEGARAGEDOOR, $entry->getIntParam());
    }

    public function testSettingIntParamWithInvalidNumber() {
        $this->expectException(\InvalidArgumentException::class);
        $this->builder->setEvent(AuditedEvent::PASSWORD_RESET())->setIntParam('zamel')->build();
    }

    public function testSettingIpv4() {
        $entry = $this->builder->setEvent(AuditedEvent::PASSWORD_RESET())->setIpv4('10.0.0.1')->build();
        $this->assertEquals('10.0.0.1', $entry->getIpv4());
    }

    public function testSettingEmptyIpv4() {
        $entry = $this->builder->setEvent(AuditedEvent::PASSWORD_RESET())->setIpv4('')->build();
        $this->assertNull($entry->getIpv4());
    }
}
