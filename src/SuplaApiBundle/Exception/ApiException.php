<?php
namespace SuplaApiBundle\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiException extends HttpException {
    public function __construct(string $message, int $statusCode = 400, \Exception $previous = null) {
    }
}
