<?php
namespace SuplaBundle\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiException extends HttpException {
    public function __construct(string $message, int $statusCode = 400, \Exception $previous = null) {
        parent::__construct($statusCode, $message, $previous, [], $statusCode);
    }

    public static function throwIf($condition, string $message, array $details = [], int $statusCode = 400) {
        if ($condition) {
            if ($details) {
                throw new ApiExceptionWithDetails($message, $details, $statusCode);
            } else {
                throw new self($message, $statusCode);
            }
        }
    }
}
