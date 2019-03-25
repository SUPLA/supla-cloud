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
use Doctrine\ORM\EntityRepository;
use ReflectionClass;
use ReflectionProperty;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Supla\SuplaAutodiscoverMock;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Tests\Integration\Traits\TestTimeProvider;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ResettableContainerInterface;

abstract class IntegrationTestCase extends WebTestCase {
    private static $dataForTests = [];

    /** @var ResettableContainerInterface */
    protected $container;
    /** @var Application */
    protected $application;

    public function prepareIntegrationTest() {
        if (!$this->hasDependencies()) {
            TestTimeProvider::reset();
            TestMailer::reset();
            SuplaServerMock::$executedCommands = [];
            SuplaAutodiscoverMock::clear();
        }
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
        self::$dataForTests = array_intersect_key(self::$dataForTests, [static::class => '']);
        $initializedAtLeastOnce = isset(self::$dataForTests[static::class]);
        if (!$initializedAtLeastOnce || $this->isLarge() || (!$this->hasDependencies() && !$this->isSmall())) {
            $this->executeCommand('doctrine:schema:drop --force');
            $this->executeCommand('doctrine:schema:create');
            $this->executeCommand('supla:oauth:create-webapp-client');
            $this->initializeDatabaseForTests();
            $reflection = new ReflectionClass($this);
            $vars = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
            $testState = [];
            foreach ($vars as $var) {
                $var->setAccessible(true);
                $testState[$var->getName()] = $var->getValue($this);
            }
            self::$dataForTests[static::class] = $testState;
        }
        if (isset(self::$dataForTests[static::class])) {
            foreach (self::$dataForTests[static::class] as $fieldName => $value) {
                EntityUtils::setField($this, $fieldName, $value);
                if ($value instanceof EntityRepository) {
                    EntityUtils::setField($value, '_em', $this->getEntityManager());
                }
            }
        }
    }

    protected function initializeDatabaseForTests() {
    }

    protected function executeCommand(string $command): string {
        $input = new StringInput("$command --env=test");
        $output = new BufferedOutput();
        $input->setInteractive(false);
        $error = $this->application->run($input, $output);
        $result = $output->fetch();
        if ($error) {
            $this->fail("Command error: $command\nReturn code: $error\nOutput:\n$result");
        }
        return $result;
    }

    protected function getDoctrine(): RegistryInterface {
        return $this->container->get('doctrine');
    }

    protected function getEntityManager(): EntityManagerInterface {
        return $this->getDoctrine()->getEntityManager();
    }

    /** @after */
    public function ensureSuplaServerValidState() {
        if (SuplaServerMock::$mockedResponses) {
            $error = 'Some of command you mocked were not used. ' . var_export(SuplaServerMock::$mockedResponses, true);
            SuplaServerMock::$mockedResponses = [];
            $this->fail($error);
        }
        if (SuplaAutodiscoverMock::$mockedResponses) {
            $error = 'Some of AD communication you mocked were not used. ' . var_export(SuplaServerMock::$mockedResponses, true);
            SuplaAutodiscoverMock::$mockedResponses = [];
            $this->fail($error);
        }
    }

    protected function createHttpsClient(bool $followRedirects = true, string $ipAddress = '1.2.3.4'): TestClient {
        $client = self::createClient(['debug' => false], [
            'HTTPS' => true,
            'HTTP_Accept' => 'application/json',
            'REMOTE_ADDR' => $ipAddress,
        ]);
        if ($followRedirects) {
            $client->followRedirects();
        }
        return $client;
    }
}
