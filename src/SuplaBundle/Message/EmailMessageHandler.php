<?php

namespace SuplaBundle\Message;

use Assert\Assertion;
use SuplaBundle\Mailer\SuplaMailer;
use Swift_Message;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class EmailMessageHandler implements MessageHandlerInterface {
    /** @var SuplaMailer */
    private $mailer;

    public function __construct(SuplaMailer $mailer) {
        $this->mailer = $mailer;
    }

    public function __invoke(EmailMessage $email) {
        $message = (new Swift_Message($email->getSubject()))
            ->setTo($email->getRecipient());
        if ($email->hasHtmlContent()) {
            $message->setBody($email->getHtmlContent(), 'text/html');
            $message->addPart($email->getTextContent(), 'text/plain');
            $logo = \Swift_Attachment::fromPath(\AppKernel::ROOT_PATH . '/../src/SuplaBundle/Resources/views/Email/supla-logo.png');
            $logo->setDisposition('inline');
            $logo->setId('logo@supla.org');
            $message->attach($logo);
        } else {
            $message->setBody($email->getTextContent(), 'text/plain');
        }
        $sent = $this->mailer->send($message);
        Assertion::true($sent, 'Could not send an e-mail.');
    }
}
