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

namespace SuplaBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ResettableContainerInterface;

abstract class IntegrationTestCase extends WebTestCase {
    /** @var ResettableContainerInterface */
    protected $container;
    /** @var Application */
    private $application;

    public function prepareIntegrationTest() {
        $client = self::createClient(['debug' => false]);
        $this->container = $client->getContainer();
        $kernel = $client->getKernel();
        $this->application = new Application($kernel);
        $this->application->setAutoExit(false);
        if (!defined('INTEGRATION_TESTS_BOOTSTRAPPED')) {
            define('INTEGRATION_TESTS_BOOTSTRAPPED', true);
            $this->executeCommand('doctrine:database:create --if-not-exists');
        }
        $this->executeCommand('doctrine:schema:drop --force');
        $this->executeCommand('doctrine:schema:create');
    }

    protected function executeCommand(string $command): string {
        $input = new StringInput("$command --env=test");
        $output = new BufferedOutput();
        $input->setInteractive(false);
        $this->application->run($input, $output);
        return $output->fetch();
    }

    protected function createAuthenticatedClient($username = 'supler@supla.org', string $password = 'supla123'): Client {
        $client = self::createClient(['debug' => false], [
            'PHP_AUTH_USER' => $username,
            'PHP_AUTH_PW' => $password,
            'HTTPS' => true,
            'HTTP_Accept' => 'application/json',
        ]);
        return $client;
    }
}
