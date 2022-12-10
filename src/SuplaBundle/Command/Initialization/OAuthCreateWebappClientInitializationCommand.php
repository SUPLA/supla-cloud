<?php
namespace SuplaBundle\Command\Initialization;

use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;
use SuplaBundle\Entity\Main\OAuth\ApiClient;
use SuplaBundle\Enums\ApiClientType;
use SuplaBundle\Repository\ApiClientRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OAuthCreateWebappClientInitializationCommand extends Command implements InitializationCommand {
    /** @var ClientManagerInterface */
    private $clientManager;
    /** @var ApiClientRepository */
    private $apiClientRepository;

    public function __construct(ClientManagerInterface $clientManager, ApiClientRepository $apiClientRepository) {
        parent::__construct();
        $this->clientManager = $clientManager;
        $this->apiClientRepository = $apiClientRepository;
    }

    protected function configure() {
        $this
            ->setName('supla:initialize:create-webapp-client')
            ->setDescription('Creates a client for web application if it does not exist yet.')
            ->setHidden(true);
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        try {
            $this->apiClientRepository->getWebappClient();
            $output->writeln('Client for webapp exists.');
        } catch (\InvalidArgumentException $e) {
            /** @var ApiClient $client */
            $client = $this->clientManager->createClient();
            $client->setAllowedGrantTypes(['password', OAuth2::GRANT_TYPE_REFRESH_TOKEN]);
            $client->setType(ApiClientType::WEBAPP());
            $this->clientManager->updateClient($client);
            $output->writeln('Client for webapp has been created.');
        }
        return 0;
    }
}
