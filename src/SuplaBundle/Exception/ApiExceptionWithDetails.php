<?php
namespace SuplaBundle\Exception;

class ApiExceptionWithDetails extends ApiException {
    /** @var array */
    private $details;

    public function __construct(string $message, array $details, int $statusCode = 400, \Exception $previous = null) {
        parent::__construct($message, $statusCode, $previous);
        $this->details = $details;
    }

    public function getDetails(): array {
        return $this->details;
    }
}
