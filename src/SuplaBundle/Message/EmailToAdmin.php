<?php

namespace SuplaBundle\Message;

class EmailToAdmin {
    /** @var EmailFromTemplate */
    private $email;

    public function __construct(EmailFromTemplate $email) {
        $this->email = $email;
    }

    public function getEmail(): EmailFromTemplate {
        return $this->email;
    }
}
