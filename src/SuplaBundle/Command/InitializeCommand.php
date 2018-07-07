<?php
namespace SuplaBundle\Command;

use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;
use SuplaApiBundle\Entity\OAuth\ApiClient;
use SuplaApiBundle\Enums\ApiClientType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class InitializeCommand extends Command {
    /** @var ClientManagerInterface */
    private $clientManager;

    public function __construct(ClientManagerInterface $clientManager) {
        parent::__construct();
        $this->clientManager = $clientManager;
    }

    protected function configure() {
        $this
            ->setName('supla:initialize')
            ->setDescription('Initializes SUPLA Cloud.');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->getApplication()->setAutoExit(false);
        $this->getApplication()->run(new StringInput('supla:check-db-connection'), $output);
        $this->getApplication()->setCatchExceptions(false);
        $this->getApplication()->run(new StringInput('doctrine:migrations:migrate --no-interaction --allow-no-migration'), $output);
        $this->createApiClientForWebapp();
    }

    private function createApiClientForWebapp() {
        /** @var ApiClient $client */
        $client = $this->clientManager->createClient();
        $client->setAllowedGrantTypes(['password', OAuth2::GRANT_TYPE_REFRESH_TOKEN]);
        $client->setType(ApiClientType::WEBAPP());
        $this->clientManager->updateClient($client);
    }
}
