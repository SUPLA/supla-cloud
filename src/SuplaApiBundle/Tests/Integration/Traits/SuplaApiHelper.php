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

namespace SuplaApiBundle\Tests\Integration\Traits;

use Assert\Assertion;
use Psr\Container\ContainerInterface;
use SuplaApiBundle\Model\ApiVersions;
use SuplaBundle\Entity\User;
use SuplaBundle\Supla\SuplaServerMockCommandsCollector;
use SuplaBundle\Tests\Integration\Traits\UserFixtures;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @property ContainerInterface $container
 */
trait SuplaApiHelper {
    use UserFixtures;

    protected function simulateAuthentication(User $user) {
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->container->get('security.token_storage')->setToken($token);
    }

    public function getSuplaServerCommands(Client $client): array {
        $profile = $client->getProfile();
        Assertion::isObject($profile, 'There is no profile available. Have you enabled it with $client->enableProfiler()?');
        /** @var SuplaServerMockCommandsCollector $suplaCommandsCollector */
        $suplaCommandsCollector = $profile->getCollector(SuplaServerMockCommandsCollector::NAME);
        return $suplaCommandsCollector->getCommands();
    }

    public function versionHeader(ApiVersions $version): array {
        return ['HTTP_X_ACCEPT_VERSION' => $version->getValue()];
    }
}
