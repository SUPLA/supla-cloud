<?php
namespace SuplaBundle\Command\Debug;

use SuplaBundle\Message\EmailFromTemplateHandler;
use SuplaBundle\Message\EmailMessageHandler;
use SuplaBundle\Message\Emails\SampleEmailNotification;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DebugMailerCommand extends Command {
    public function __construct(
        private EmailFromTemplateHandler $emailFromTemplateHandler,
        private EmailMessageHandler $emailMessageHandler,
    ) {
        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('supla:debug:mailer')
            ->setDescription('Sends a test e-mail message.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);
        $io->title('SUPLA Mailer debugger');
        $email = $io->ask('Recipient e-mail address: ');
        $message = new SampleEmailNotification($email);
        $emailMessage = $this->emailFromTemplateHandler->renderEmailMessage($message);
        $sentMessage = $this->emailMessageHandler->send($emailMessage);
        $io->success('Message sent!');
        if ($output->isVerbose()) {
            $io->section('Debug info');
            $io->writeln($sentMessage->getDebug());
        }
        return 0;
    }
}
