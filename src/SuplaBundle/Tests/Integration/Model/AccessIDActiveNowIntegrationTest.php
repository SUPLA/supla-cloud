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

namespace SuplaBundle\Tests\Integration\Model;

use SuplaBundle\Entity\AccessID;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\User;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\UserFixtures;

/** @small */
class AccessIDActiveNowIntegrationTest extends IntegrationTestCase {
    use UserFixtures;

    /** @var User */
    private $user;

    protected function initializeDatabaseForTests() {
        $this->executeCommand('doctrine:migrations:execute 20220309061812');
        $this->user = $this->createConfirmedUser();
    }

    /** @dataProvider activeNowAccessIds */
    public function testActiveNowAccessIds(callable $accessIdFactory, bool $expectActive = true) {
        $accessId = $accessIdFactory(new AccessID($this->user));
        $accessId->setPassword('xxx');
        $this->getEntityManager()->persist($this->user);
        $this->getEntityManager()->persist($accessId);
        $this->getEntityManager()->flush();
        $query = 'SELECT is_now_active FROM supla_v_accessid_active WHERE id = ' . $accessId->getId();
        $result = $this->getEntityManager()->getConnection()->executeQuery($query);
        $isActive = $result->fetchOne();
        if ($expectActive) {
            $this->assertTrue(!!$isActive);
        } else {
            $this->assertFalse(!!$isActive);
        }
    }

    public function activeNowAccessIds() {
        return [
            'no constraints' => [function (AccessID $aid) {
                return $aid;
            }],
            'full activeHours spec' => [function (AccessID $aid) {
                $fullSpec = range(1, 7);
                $fullSpec = array_flip($fullSpec);
                $fullSpec = array_map(function ($v) {
                    return range(0, 23);
                }, $fullSpec);
                $aid->setActiveHours($fullSpec);
                return $aid;
            }],
            'active in Europe/Warsaw' => [function (AccessID $aid) {
                $timezone = new \DateTimeZone('Europe/Warsaw');
                $aid->getUser()->setTimezone($timezone->getName());
                $weekday = (new \DateTime('now', $timezone))->format('N');
                $hour = (new \DateTime('now', $timezone))->format('G');
                $aid->setActiveHours([$weekday => [$hour]]);
                return $aid;
            }],
            'active in Europe/Warsaw with more conditions' => [function (AccessID $aid) {
                $timezone = new \DateTimeZone('Europe/Warsaw');
                $aid->getUser()->setTimezone($timezone->getName());
                $weekday = (new \DateTime('now', $timezone))->format('N');
                $hour = (new \DateTime('now', $timezone))->format('G');
                $anotherWeekday = $weekday == 1 ? 2 : 1;
                $aid->setActiveHours([$weekday => [1, 2, 3, 4, $hour], $anotherWeekday => [11, 12, 13]]);
                return $aid;
            }],
            'active in Australia/Adelaide' => [function (AccessID $aid) {
                $timezone = new \DateTimeZone('Australia/Adelaide');
                $aid->getUser()->setTimezone($timezone->getName());
                $weekday = (new \DateTime('now', $timezone))->format('N');
                $hour = (new \DateTime('now', $timezone))->format('G');
                $aid->setActiveHours([$weekday => [$hour]]);
                return $aid;
            }],
            'inactive hour' => [function (AccessID $aid) {
                $timezone = new \DateTimeZone('Europe/Warsaw');
                $aid->getUser()->setTimezone($timezone->getName());
                $weekday = (new \DateTime('now', $timezone))->format('N');
                $hour = (new \DateTime('now', $timezone))->format('G');
                $aid->setActiveHours([$weekday => [$hour == 0 ? 1 : 0]]);
                return $aid;
            }, false],
            'inactive weekday' => [function (AccessID $aid) {
                $timezone = new \DateTimeZone('Europe/Warsaw');
                $aid->getUser()->setTimezone($timezone->getName());
                $weekday = (new \DateTime('now', $timezone))->format('N');
                $hour = (new \DateTime('now', $timezone))->format('G');
                $weekday = $weekday == 1 ? 2 : 1;
                $aid->setActiveHours([$weekday => [$hour]]);
                return $aid;
            }, false],
            'active from in the past' => [function (AccessID $aid) {
                $timezone = new \DateTimeZone('Europe/Warsaw');
                $aid->getUser()->setTimezone($timezone->getName());
                $aid->setActiveFrom(new \DateTime('-10 minutes', $timezone));
                return $aid;
            }],
            'active from in the future' => [function (AccessID $aid) {
                $timezone = new \DateTimeZone('Europe/Warsaw');
                $aid->getUser()->setTimezone($timezone->getName());
                $aid->setActiveFrom(new \DateTime('+10 minutes', $timezone));
                return $aid;
            }, false],
            'active to in the future' => [function (AccessID $aid) {
                $timezone = new \DateTimeZone('Europe/Warsaw');
                $aid->getUser()->setTimezone($timezone->getName());
                $aid->setActiveTo(new \DateTime('+10 minutes', $timezone));
                return $aid;
            }],
            'active to in the past' => [function (AccessID $aid) {
                $timezone = new \DateTimeZone('Europe/Warsaw');
                $aid->getUser()->setTimezone($timezone->getName());
                $aid->setActiveTo(new \DateTime('-10 minutes', $timezone));
                return $aid;
            }, false],
            'all conditions are ok' => [function (AccessID $aid) {
                $timezone = new \DateTimeZone('Australia/Adelaide');
                $aid->getUser()->setTimezone($timezone->getName());
                $weekday = (new \DateTime('now', $timezone))->format('N');
                $hour = (new \DateTime('now', $timezone))->format('G');
                $aid->setActiveHours([$weekday => [$hour]]);
                $aid->setActiveFrom(new \DateTime('-10 minutes', $timezone));
                $aid->setActiveTo(new \DateTime('+10 minutes', $timezone));
                return $aid;
            }],
            'days are ok, but active from in the future' => [function (AccessID $aid) {
                $timezone = new \DateTimeZone('Australia/Adelaide');
                $aid->getUser()->setTimezone($timezone->getName());
                $weekday = (new \DateTime('now', $timezone))->format('N');
                $hour = (new \DateTime('now', $timezone))->format('G');
                $aid->setActiveHours([$weekday => [$hour]]);
                $aid->setActiveFrom(new \DateTime('+10 minutes', $timezone));
                return $aid;
            }, false],
            'invalid user timezone' => [function (AccessID $aid) {
                EntityUtils::setField($aid->getUser(), 'timezone', 'Unicorn');
                $fullSpec = range(1, 7);
                $fullSpec = array_flip($fullSpec);
                $fullSpec = array_map(function ($v) {
                    return range(0, 23);
                }, $fullSpec);
                $aid->setActiveHours($fullSpec);
                return $aid;
            }],
        ];
    }
}
