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

use Doctrine\ORM\EntityManagerInterface;
use SuplaApiBundle\Tests\Integration\TestMailer;
use SuplaBundle\Tests\Integration\Traits\TestTimeProvider;
use Symfony\Bridge\Doctrine\RegistryInterface;
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
        TestTimeProvider::reset();
        TestMailer::reset();
        $client = self::createClient(['debug' => false]);
        $this->container = $client->getContainer();
        $kernel = $client->getKernel();
        $this->application = new Application($kernel);
        $this->application->setAutoExit(false);
        if (!defined('INTEGRATION_TESTS_BOOTSTRAPPED')) {
            define('INTEGRATION_TESTS_BOOTSTRAPPED', true);
            $this->executeCommand('doctrine:database:create --if-not-exists');
        }
        $this->clearDatabase();
    }

    protected function clearDatabase() {
        $this->executeCommand('doctrine:schema:drop --force');
        $this->executeCommand('doctrine:schema:create');
        $this->executeCommand('supla:oauth:create-webapp-client');
    }

    protected function executeCommand(string $command): string {
        $input = new StringInput("$command --env=test");
        $output = new BufferedOutput();
        $input->setInteractive(false);
        $this->application->run($input, $output);
        $result = $output->fetch();
        return $result;
    }

    protected function getDoctrine(): RegistryInterface {
        return $this->container->get('doctrine');
    }

    protected function getEntityManager(): EntityManagerInterface {
        return $this->getDoctrine()->getEntityManager();
    }
}
