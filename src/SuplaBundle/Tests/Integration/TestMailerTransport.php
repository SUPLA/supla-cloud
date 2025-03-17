<?php
namespace SuplaBundle\Tests\Integration;

use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

class TestMailerTransport implements TransportInterface {
    private static $messages = [];

    /** @return Email[] */
    public static function getMessages(): array {
        return self::$messages;
    }

    public function send(RawMessage $message, Envelope $envelope = null): ?SentMessage {
        self::$messages[] = $message;
        return new SentMessage($message, $envelope ?: Envelope::create($message));
    }

    public static function reset() {
        self::$messages = [];
    }

    public function __toString(): string {
        return 'test mailer';
    }
}
