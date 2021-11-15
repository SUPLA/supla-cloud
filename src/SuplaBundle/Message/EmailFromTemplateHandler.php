<?php

namespace SuplaBundle\Message;

use SuplaBundle\Mailer\SuplaMailer;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class EmailFromTemplateHandler implements MessageHandlerInterface {
    /** @var SuplaMailer */
    private $mailer;

    public function __construct(SuplaMailer $mailer) {
        $this->mailer = $mailer;
    }

    public function __invoke(EmailFromTemplate $email) {
        echo "JEEEEEEEEEEEEEEE";
    }
}
