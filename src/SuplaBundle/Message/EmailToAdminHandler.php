<?php

namespace SuplaBundle\Message;

use SuplaBundle\Entity\EntityUtils;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class EmailToAdminHandler implements MessageHandlerInterface {
    /** @var MessageBusInterface */
    private $messageBus;
    /** @var string */
    private $adminEmail;

    public function __construct(MessageBusInterface $messageBus, ?string $adminEmail) {
        $this->messageBus = $messageBus;
        $this->adminEmail = $adminEmail;
    }

    public function __invoke(EmailToAdmin $emailToAdmin) {
        if ($this->adminEmail) {
            $email = $emailToAdmin->getEmail();
            EntityUtils::setField($email, 'recipient', $this->adminEmail);
            $this->messageBus->dispatch($email);
        }
    }
}
