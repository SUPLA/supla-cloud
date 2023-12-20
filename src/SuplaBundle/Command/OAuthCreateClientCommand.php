<?php
namespace SuplaBundle\Command;

use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OAuthCreateClientCommand extends Command {

    private $clientManager;

    public function __construct(ClientManagerInterface $clientManager) {
        parent::__construct();
        $this->clientManager = $clientManager;
    }

    protected function configure() {
        parent::configure();

        $this
            ->setName('supla:oauth:create-client [--redirect-uri=...]')
            ->setDescription('OAuth Create Client')
            ->addOption(
                'redirect-uri',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Sets redirect uri for client. Use this option multiple times to set multiple redirect URIs.',
                null
            );
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);
        $io->title('Client Credentials');
        // Create a new client
        $client = $this->clientManager->createClient();
        $client->setRedirectUris($input->getOption('redirect-uri'));
        $client->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE]);
        // Save the client
        $this->clientManager->updateClient($client);
        // Give the credentials back to the user
        $headers = ['Client ID', 'Client Secret'];
        $rows = [
            [$client->getPublicId(), $client->getSecret()],
        ];
        $io->table($headers, $rows);
        return 0;
    }
}
