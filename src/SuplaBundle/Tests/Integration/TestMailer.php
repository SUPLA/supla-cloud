<?php
namespace SuplaBundle\Tests\Integration;

use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

class TestMailer implements MailerInterface {
    private static $messages = [];

    /** @return Email[] */
    public static function getMessages(): array {
        return self::$messages;
    }

    public function send(RawMessage $message, Envelope $envelope = null): void {
        self::$messages[] = $message;
    }

    public static function reset() {
        self::$messages = [];
    }
}
