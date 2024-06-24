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

namespace SuplaBundle\Tests\Supla;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use SuplaBundle\Entity\Main\OAuth\ApiClient;
use SuplaBundle\Model\LocalSuplaCloud;
use SuplaBundle\Model\TargetSuplaCloud;
use SuplaBundle\Model\UserManager;
use SuplaBundle\Repository\SettingsStringRepository;
use SuplaBundle\Supla\SuplaAutodiscoverMock;
use SuplaBundle\Supla\SuplaBrokerHttpClient;

/**
 * We are testing the mocked implementation here in order to be sure in behaves correctly in integration tests.
 */
class SuplaAutodiscoverMockTest extends TestCase {
    /** @var UserManager|MockObject */
    private $userManager;
    /** @var SuplaAutodiscoverMock */
    private $autodiscover;

    /** @before */
    public function init() {
        $this->userManager = $this->createMock(UserManager::class);
        $this->autodiscover = new SuplaAutodiscoverMock(
            new LocalSuplaCloud('https://supla.local'),
            $this->userManager,
            $this->createMock(LoggerInterface::class),
            $this->createMock(SettingsStringRepository::class),
            $this->createMock(SuplaBrokerHttpClient::class)
        );
        SuplaAutodiscoverMock::clear();
    }

    public function testCheckingIfUserExists() {
        SuplaAutodiscoverMock::$userMapping['ala@supla.org'] = 'supla.local';
        $this->assertTrue($this->autodiscover->userExists('ala@supla.org'));
        $this->assertFalse($this->autodiscover->userExists('ela@supla.org'));
    }

    public function testGetAuthServerForUser() {
        SuplaAutodiscoverMock::$userMapping['ala@supla.org'] = 'supla2.local';
        $alaServer = $this->autodiscover->getAuthServerForUser('ala@supla.org');
        $elaServer = $this->autodiscover->getAuthServerForUser('ela@supla.org');
        $this->assertFalse($alaServer->isLocal());
        $this->assertEquals('https://supla2.local', $alaServer->getAddress());
        $this->assertTrue($elaServer->isLocal());
        $this->assertEquals('https://supla.local', $elaServer->getAddress());
    }

    public function testFetchTargetCloudClientId() {
        SuplaAutodiscoverMock::$clientMapping['https://target.cloud']['public-id']['clientId'] = 'local-id';
        $targetCloud = new TargetSuplaCloud('https://target.cloud', false);
        $this->assertEquals('local-id', $this->autodiscover->getTargetCloudClientId($targetCloud, 'public-id'));
    }

    public function testFetchTargetCloudClientSecret() {
        SuplaAutodiscoverMock::$clientMapping['https://target.org']['public-id'] = ['clientId' => 'local-id', 'secret' => 'local-secret'];
        SuplaAutodiscoverMock::$publicClients['public-id'] = ['secret' => 'public-secret', 'name' => 'App'];
        $targetCloud = new TargetSuplaCloud('https://target.org', false);
        $client = $this->createMock(ApiClient::class);
        $client->method('getPublicId')->willReturn('public-id');
        $client->method('getSecret')->willReturn('public-secret');
        $this->assertEquals(
            ['mappedClientId' => 'local-id', 'secret' => 'local-secret'],
            $this->autodiscover->fetchTargetCloudClientSecret($client, $targetCloud)
        );
    }

    public function testFetchTargetCloudClientSecretWhenWrongPublicSecret() {
        SuplaAutodiscoverMock::$clientMapping['https://target.org']['public-id'] = ['clientId' => 'local-id', 'secret' => 'local-secret'];
        SuplaAutodiscoverMock::$publicClients['public-id'] = ['secret' => 'public-secret', 'name' => 'App'];
        $targetCloud = new TargetSuplaCloud('https://target.org', false);
        $client = $this->createMock(ApiClient::class);
        $client->method('getPublicId')->willReturn('public-id');
        $client->method('getSecret')->willReturn('wrong-secret');
        $this->assertFalse($this->autodiscover->fetchTargetCloudClientSecret($client, $targetCloud));
    }

    public function testFetchTargetCloudClientSecretWhenWrongPublicId() {
        SuplaAutodiscoverMock::$clientMapping['https://target.org']['public-id'] = ['clientId' => 'local-id', 'secret' => 'local-secret'];
        SuplaAutodiscoverMock::$publicClients['public-id'] = ['secret' => 'public-secret', 'name' => 'App'];
        $targetCloud = new TargetSuplaCloud('https://target.org', false);
        $client = $this->createMock(ApiClient::class);
        $client->method('getPublicId')->willReturn('wrong-id');
        $client->method('getSecret')->willReturn('public-secret');
        $this->assertFalse($this->autodiscover->fetchTargetCloudClientSecret($client, $targetCloud));
    }

    public function testFetchClientPublicIdBasedOnMappedId() {
        SuplaAutodiscoverMock::$clientMapping['https://supla.local']['public-id'] = ['clientId' => 'local-id', 'secret' => 'local-secret'];
        SuplaAutodiscoverMock::$publicClients['public-id'] = ['secret' => 'public-secret', 'name' => 'App'];
        $this->assertEquals(
            'public-id',
            $this->autodiscover->getPublicIdBasedOnMappedId('local-id')
        );
    }
}
