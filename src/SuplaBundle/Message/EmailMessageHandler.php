<?php

namespace SuplaBundle\Message;

use Assert\Assertion;
use SuplaBundle\Mailer\SuplaMailer;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Mime\Email;

class EmailMessageHandler implements MessageHandlerInterface {
    public function __construct(private SuplaMailer $mailer) {
    }

    public function __invoke(EmailMessage $email) {
        $this->send($email);
    }

    public function send(EmailMessage $email): SentMessage {
        $message = (new Email())
            ->subject($email->getSubject())
            ->to($email->getRecipient())
            ->text($email->getTextContent());
        if ($email->hasHtmlContent()) {
            $message->html($email->getHtmlContent());
            $message->embedFromPath(\AppKernel::ROOT_PATH . '/../src/SuplaBundle/Resources/views/Email/supla-logo.png', 'logo@supla.org');
        }
        $sentMessage = $this->mailer->send($message);
        Assertion::notNull($sentMessage, 'Could not send an e-mail.');
        return $sentMessage;
    }
}
