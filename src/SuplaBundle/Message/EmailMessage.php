<?php

namespace SuplaBundle\Message;

class EmailMessage {
    /** @var string */
    private $recipient;
    /** @var string */
    private $subject;
    /** @var string */
    private $textContent;
    /** @var string|null */
    private $htmlContent;

    public function __construct(string $recipient, string $subject, string $textContent, ?string $htmlContent = null) {
        $this->recipient = $recipient;
        $this->subject = $subject;
        $this->textContent = $textContent;
        $this->htmlContent = $htmlContent;
    }

    public function getRecipient(): string {
        return $this->recipient;
    }

    public function getSubject(): string {
        return $this->subject;
    }

    public function getTextContent(): string {
        return $this->textContent;
    }

    public function getHtmlContent(): ?string {
        return $this->htmlContent;
    }

    public function hasHtmlContent(): bool {
        return !!$this->htmlContent;
    }
}
