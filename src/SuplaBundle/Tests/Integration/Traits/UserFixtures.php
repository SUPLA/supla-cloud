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

namespace SuplaBundle\Tests\Integration\Traits;

use DateTime;
use SuplaBundle\Auth\OAuthScope;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\ClientApp;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\Location;
use SuplaBundle\Entity\Main\OAuth\AccessToken;
use SuplaBundle\Entity\Main\Scene;
use SuplaBundle\Entity\Main\SceneOperation;
use SuplaBundle\Entity\Main\Schedule;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelFunctionBitsFlist;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Enums\IoDeviceFlags;
use SuplaBundle\Enums\ScheduleMode;
use SuplaBundle\Model\LocationManager;
use SuplaBundle\Model\Schedule\ScheduleManager;
use SuplaBundle\Model\UserManager;
use SuplaBundle\Repository\ApiClientRepository;
use SuplaBundle\Tests\AnyFieldSetter;
use SuplaBundle\Utils\StringUtils;

trait UserFixtures {
    protected function createConfirmedUser(string $username = 'supler@supla.org', string $password = 'supla123'): User {
        $userManager = self::$container->get(UserManager::class);
        $user = new User();
        $user->setEmail($username);
        $user->setPlainPassword($password);
        $userManager->create($user);
        $userManager->setPassword($password, $user, true);
        $userManager->confirm($user->getToken());

        // create valid access token to speed up integration tests
        $webappClient = self::$container->get(ApiClientRepository::class)->getWebappClient();
        $token = new AccessToken();
        EntityUtils::setField($token, 'client', $webappClient);
        EntityUtils::setField($token, 'user', $user);
        EntityUtils::setField($token, 'expiresAt', (new DateTime('2035-01-01T00:00:00'))->getTimestamp());
        EntityUtils::setField($token, 'token', base64_encode($username));
        EntityUtils::setField($token, 'scope', (string)(new OAuthScope(OAuthScope::getSupportedScopes())));
        $em = self::$container->get('doctrine')->getManager();
        $em->persist($token);
        $em->flush();

        return $user;
    }

    protected function createLocation(User $user): Location {
        $location = self::$container->get(LocationManager::class)->createLocation($user);
        $this->getEntityManager()->persist($location);
        $this->getEntityManager()->flush();
        return $location;
    }

    protected function createClientApp(User $user): ClientApp {
        $clientApp = new ClientApp();
        $clientApp->setEnabled(true);
        AnyFieldSetter::set($clientApp, [
            'guid' => StringUtils::randomString(10),
            'regDate' => new \DateTime(),
            'lastAccessDate' => new \DateTime(),
            'softwareVersion' => '1.' . rand(0, 100),
            'protocolVersion' => rand(0, 100),
            'user' => $user,
            'name' => 'iPhone ' . rand(0, 100),
        ]);
        $this->getEntityManager()->persist($clientApp);
        $this->getEntityManager()->flush();
        return $clientApp;
    }

    protected function createDeviceSonoff(Location $location): IODevice {
        $device = $this->createDevice($location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::THERMOMETERDS18B20, ChannelFunction::THERMOMETER],
            [ChannelType::ACTION_TRIGGER, ChannelFunction::ACTION_TRIGGER],
        ]);
        $at = $device->getChannels()[2];
        $at->setParam1($device->getChannels()[0]->getId());
        $config = ['actionTriggerCapabilities' => ['TURN_ON', 'TURN_OFF', 'TOGGLE_x1', 'TOGGLE_x2', 'TOGGLE_x3', 'TOGGLE_x4', 'TOGGLE_x5']];
        EntityUtils::setField($at, 'properties', json_encode($config));
        $this->getEntityManager()->persist($at);
        $this->getEntityManager()->flush();
        return $device;
    }

    protected function createDeviceFull(Location $location): IODevice {
        return $this->createDevice($location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEDOORLOCK],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEGATE],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEROLLERSHUTTER],
            [ChannelType::THERMOMETERDS18B20, ChannelFunction::THERMOMETER],
        ]);
    }

    protected function createDevice(Location $location, array $channelTypes): IODevice {
        $fieldSetter = function ($field, $type) {
            $this->{$field} = $type;
        };

        $device = new IODevice();
        $fieldSetter->call($device, 'guid', rand(0, 9999999));
        $fieldSetter->call($device, 'regDate', new DateTime());
        $fieldSetter->call($device, 'lastConnected', new DateTime());
        $fieldSetter->call($device, 'regIpv4', implode('.', [rand(0, 255), rand(0, 255), rand(0, 255), rand(0, 255)]));
        $fieldSetter->call($device, 'softwareVersion', '2.' . rand(0, 50));
        $fieldSetter->call($device, 'protocolVersion', '2.' . rand(0, 50));
        $fieldSetter->call($device, 'location', $location);
        $fieldSetter->call($device, 'user', $location->getUser());
        $fieldSetter->call($device, 'flags', IoDeviceFlags::ENTER_CONFIGURATION_MODE_AVAILABLE);
        $fieldSetter->call($device, 'userConfig', '{"statusLed": "ON_WHEN_CONNECTED"}');
        $this->getEntityManager()->persist($device);

        foreach ($channelTypes as $channelNumber => $channelData) {
            $channel = new IODeviceChannel();
            $fieldSetter->call($channel, 'iodevice', $device);
            $fieldSetter->call($channel, 'user', $location->getUser());
            $fieldSetter->call($channel, 'type', $channelData[0]);
            $fieldSetter->call($channel, 'function', $channelData[1]);
            $fieldSetter->call($channel, 'channelNumber', $channelNumber++);
            if (in_array($channel->getType()->getId(), [ChannelType::RELAY, ChannelType::HVAC])) {
                $fieldSetter->call($channel, 'funcList', ChannelFunctionBitsFlist::getAllFeaturesFlag());
            }
            $fieldSetter->call($channel, 'flags', 0b1111111111);
            $this->getEntityManager()->persist($channel);
            $this->getEntityManager()->flush();
        }
        $this->getEntityManager()->refresh($device);
        return $device;
    }

    protected function createSchedule(ActionableSubject $subject, string $timeExpression, array $data = []): Schedule {
        $subject = $this->freshEntity($subject);
        $schedule = new Schedule($subject->getUser(), array_merge([
            'subject' => $subject,
            'mode' => ScheduleMode::ONCE,
            'config' => [['crontab' => $timeExpression, 'action' => ['id' => ChannelFunctionAction::TURN_ON]]],
        ], $data));
        $schedule->setEnabled(true);
        $this->persist($schedule);
        self::$container->get(ScheduleManager::class)->generateScheduledExecutions($schedule);
        return $schedule;
    }

    protected function createScene(Location $location, ActionableSubject...$subjectForOperation): Scene {
        $scene = new Scene($this->freshEntity($location));
        $operations = array_map(
            fn(ActionableSubject $subject) => new SceneOperation(
                $this->freshEntity($subject),
                $subject->getFunction()->getDefaultPossibleActions()[0]
            ),
            $subjectForOperation
        );
        $scene->setOpeartions($operations);
        $this->persist($scene);
        return $scene;
    }
}
