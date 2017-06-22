<?php
namespace SuplaBundle\Tests\Integration;

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
        $client = self::createClient();
        $this->container = $client->getContainer();
        $kernel = $client->getKernel();
        $this->application = new Application($kernel);
        $this->application->setAutoExit(false);
        if (!defined('INTEGRATION_TESTS_BOOTSTRAPPED')) {
            define('INTEGRATION_TESTS_BOOTSTRAPPED', true);
            $this->executeCommand('doctrine:database:drop --force --if-exists');
            $this->executeCommand('doctrine:database:create');
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
}
