<?php
namespace SuplaBundle\Tests\Integration;

class TestMailer extends \Swift_Mailer {
    private static $messages = [];

    public function __construct() {
        parent::__construct(new \Swift_NullTransport());
    }

    /** @return \Swift_Mime_SimpleMessage[] */
    public static function getMessages(): array {
        return self::$messages;
    }

    public function send(\Swift_Mime_SimpleMessage $message, &$failedRecipients = null) {
        self::$messages[] = $message;
    }

    public static function reset() {
        self::$messages = [];
    }
}
