<?php

namespace SuplaBundle\Tests\Integration\Traits;

use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

trait ResponseAssertions {
    /**
     * Asserts that HTTP response's status code is equal to expected or belongs to expected family.
     * @param $expectedStatus int|string representing code (eg. 200, 404) or string representing code family (eg. '2xx', '4xx')
     * @param Response $clientResponse
     */
    protected function assertStatusCode($expectedStatus, Response $clientResponse) {
        $actualStatus = $clientResponse->getStatusCode();
        $fullStatusLine = $this->getResponseStatusLine($clientResponse);
        $message = "Response status $actualStatus isn't %s: $fullStatusLine. Response content: \n" . $clientResponse->getContent();
        if (is_int($expectedStatus)) {
            Assert::assertEquals($expectedStatus, $actualStatus, sprintf($message, $expectedStatus));
        } elseif (preg_match('/^[1-5]xx$/', $expectedStatus)) {
            $firstDigit = intval($expectedStatus[0]);
            Assert::assertEquals($firstDigit, floor($actualStatus / 100), sprintf($message, $expectedStatus));
        } elseif ($expectedStatus === false) {
            Assert::assertNotEquals('2', floor($actualStatus / 100), sprintf($message, 'non-successful'));
        }
    }

    private function getResponseStatusLine(Response $response): string {
        $fullResponse = (string)$response;
        return trim(strtok($fullResponse, "\n"));
    }
}
