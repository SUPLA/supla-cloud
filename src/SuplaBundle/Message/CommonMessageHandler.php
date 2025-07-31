<?php

namespace SuplaBundle\Message;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class CommonMessageHandler implements MessageHandlerInterface {
    public function __construct(private MessageBusInterface $bus) {
    }

    public function __invoke(CommonMessage $message): void {
        $this->send($message);
    }

    private function send(CommonMessage $msg): void {
        $this->bus->dispatch(
            new EmailFromTemplateAsync($msg->getTemplate(), $msg->getUser(), $msg->getData(), $msg->getBurnAfterSeconds())
        );
        $this->bus->dispatch(
            new PushMessageFromTemplate($msg->getTemplate(), $msg->getUser(), $msg->getData(), $msg->getBurnAfterSeconds())
        );
    }
}
