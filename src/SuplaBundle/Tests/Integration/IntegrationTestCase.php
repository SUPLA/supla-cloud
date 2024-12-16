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
use Doctrine\Persistence\ManagerRegistry;
use ReflectionClass;
use ReflectionObject;
use ReflectionProperty;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Supla\SuplaAutodiscoverMock;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Tests\Integration\Traits\TestSuplaHttpClient;
use SuplaBundle\Tests\Integration\Traits\TestTimeProvider;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

abstract class IntegrationTestCase extends WebTestCase {
    private static $dataForTests = [];

    /** @var Application */
    protected $application;

    public function prepareIntegrationTest() {
        if (!$this->requires()) {
            TestTimeProvider::reset();
            TestMailer::reset();
            SuplaServerMock::$executedCommands = [];
            SuplaAutodiscoverMock::clear();
        }
        $client = self::createClient(['debug' => false]);
        $kernel = $client->getKernel();
        $this->application = new Application($kernel);
        $this->application->setAutoExit(false);
        if (!defined('INTEGRATION_TESTS_BOOTSTRAPPED')) {
            define('INTEGRATION_TESTS_BOOTSTRAPPED', true);
            $this->executeCommand('doctrine:database:drop --if-exists --force');
            $this->executeCommand('doctrine:database:create --if-not-exists');
            $this->executeCommand('messenger:setup-transports');
        }
        $this->clearDatabase();
    }

    protected function clearDatabase() {
        self::$dataForTests = array_intersect_key(self::$dataForTests, [static::class => '']);
        $initializedAtLeastOnce = isset(self::$dataForTests[static::class]);
        if (!$initializedAtLeastOnce || $this->isLarge() || (!$this->requires() && !$this->isSmall())) {
            $this->executeCommand('doctrine:schema:drop --force --full-database --em=default');
            $this->executeCommand('doctrine:schema:drop --force --full-database --em=measurement_logs');
            $this->executeCommand('doctrine:schema:create --em=default');
            $this->executeCommand('doctrine:schema:create --em=measurement_logs');
            $this->executeCommand('supla:initialize:create-sql-procedures-and-views');
            $this->executeCommand('supla:initialize:create-webapp-client');
            $this->getEntityManager()->getConnection()->executeQuery('TRUNCATE supla_email_notifications;');
            $this->initializeDatabaseForTests();
            $reflection = new ReflectionClass($this);
            $vars = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
            $testState = [];
            foreach ($vars as $var) {
                $var->setAccessible(true);
                $testState[$var->getName()] = $var->getValue($this);
            }
            self::$dataForTests[static::class] = $testState;
            SuplaServerMock::reset();
        }
        if (isset(self::$dataForTests[static::class])) {
            foreach (self::$dataForTests[static::class] as $fieldName => $value) {
                if (is_object($value) && strpos(get_class($value), 'SuplaBundle\Entity') === 0) {
                    $value = $this->freshEntity($value);
                }
                EntityUtils::setField($this, $fieldName, $value);
                if ($value instanceof EntityRepository) {
                    EntityUtils::setField($value, '_em', $this->getEntityManager());
                }
            }
        }
    }

    protected function initializeDatabaseWithMigrations() {
        $this->executeCommand('doctrine:database:drop --force');
        $this->executeCommand('doctrine:database:create --if-not-exists');
        $this->executeCommand('supla:initialize');
    }

    protected function initializeDatabaseForTests() {
    }

    /** @param array|string $command */
    protected function executeCommand($command, ?TestClient $client = null): string {
        if (is_array($command)) {
            $command['--env'] = 'test';
            $input = new ArrayInput($command);
        } else {
            $input = new StringInput("$command --env=test");
        }
        $application = $this->application;
        if ($client) {
            $application = new Application($client->getKernel());
            $application->setAutoExit(false);
        }
        $output = new BufferedOutput();
        $input->setInteractive(false);
        $error = $application->run($input, $output);
        $result = $output->fetch();
        if ($error) {
            $this->fail("Command error: $command\nReturn code: $error\nOutput:\n$result");
        }
        return $result;
    }

    protected function getDoctrine(): ManagerRegistry {
        return self::$container->get('doctrine');
    }

    protected function getEntityManager($name = null): EntityManagerInterface {
        return $this->getDoctrine()->getManager($name);
    }

    protected function persist($entity) {
        $this->getEntityManager()->persist($entity);
        $this->flush();
        return $this->freshEntity($entity);
    }

    protected function flush() {
        $this->getEntityManager()->flush();
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
        if (TestSuplaHttpClient::$mockedResponses) {
            $error = 'Some of requests you mocked were not used. ' . var_export(TestSuplaHttpClient::$mockedResponses, true);
            TestSuplaHttpClient::$mockedResponses = [];
            $this->fail($error);
        }
    }

    /**
     * @see https://stackoverflow.com/a/37864440/878514
     * @after
     */
    public function freeUpMemory() {
        if (self::$container) {
            self::$container->reset();
            self::$container = null;
        }
        $this->application = null;
        $refl = new ReflectionObject($this);
        foreach ($refl->getProperties() as $prop) {
            if (!$prop->isStatic() && 0 !== strpos($prop->getDeclaringClass()->getName(), 'PHPUnit_')) {
                $prop->setAccessible(true);
                $prop->setValue($this, null);
            }
        }
        gc_collect_cycles();
//         print sprintf("\nMemory usage: %d MB, after test: %s\n", round(memory_get_usage() / 1024 / 1024), $this->getName());
    }

    /** @afterClass */
    public static function freeUpSavedTestData() {
        self::$dataForTests = [];
    }

    protected function createHttpsClient(bool $followRedirects = true, string $ipAddress = '1.2.3.4'): TestClient {
        self::ensureKernelShutdown();
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

    /**
     * Insulation saves memory but can't read static state of the app.
     * @see https://jolicode.com/blog/you-may-have-memory-leaking-from-php-7-and-symfony-tests
     */
    protected function createHttpsInsulatedClient() {
        $client = $this->createHttpsClient();
        $client->insulate();
        return $client;
    }

    protected function createInsulatedClient(array $options = [], array $server = []) {
        self::ensureKernelShutdown();
        $client = self::createClient($options, $server);
        $client->insulate();
        return $client;
    }

    protected function freshEntity($entity) {
        return $this->freshEntityById(get_class($entity), $entity->getId());
    }

    protected function freshEntityById(string $class, int $id) {
        return $this->getEntityManager()->find($class, $id);
    }

    protected function freshChannelById(int $id): IODeviceChannel {
        return $this->getEntityManager()->find(IODeviceChannel::class, $id);
    }

    protected function flushMessagesQueue(?TestClient $client = null) {
        $maxIterations = 5;
        $messagesQuery = 'SELECT COUNT(*) FROM supla_email_notifications WHERE queue_name != "supla-server"';
        while (($count = $this->getEntityManager()->getConnection()->fetchOne($messagesQuery)) > 0) {
            if (!$maxIterations--) {
                $this->fail('Could not flush the messages queue. Error in handler?');
            }
            $this->executeCommand("messenger:consume --time-limit 1 --limit $count --no-interaction -vv", $client);
        }
    }
}
