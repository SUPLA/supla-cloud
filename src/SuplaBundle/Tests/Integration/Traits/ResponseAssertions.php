<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace SuplaBundle\Tests\Integration\Traits;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

trait ResponseAssertions {
    /**
     * Asserts that HTTP response's status code is equal to expected or belongs to expected family.
     * @param $expectedStatus int|string|int[] representing code (eg. 200, 404) or string representing code family (eg. '2xx', '4xx')
     * @param Response|KernelBrowser $clientResponse
     */
    protected function assertStatusCode($expectedStatus, $clientResponse, string $message = '') {
        if ($clientResponse instanceof KernelBrowser) {
            $clientResponse = $clientResponse->getResponse();
        }
        $actualStatus = $clientResponse->getStatusCode();
        $fullStatusLine = $this->getResponseStatusLine($clientResponse);
        $message = ($message ? $message . PHP_EOL : '') . "Response status $actualStatus isn't %s: $fullStatusLine. Response content: \n"
            . str_replace('%', 'p', $clientResponse->getContent());
        if (is_numeric($expectedStatus)) {
            $this->assertEquals(intval($expectedStatus), $actualStatus, sprintf($message, $expectedStatus));
        } elseif (is_array($expectedStatus)) {
            $this->assertContains($actualStatus, $expectedStatus, sprintf($message, implode('|', $expectedStatus)));
        } elseif (preg_match('/^[1-5]xx$/i', $expectedStatus)) {
            $firstDigit = intval($expectedStatus[0]);
            $this->assertEquals($firstDigit, floor($actualStatus / 100), sprintf($message, $expectedStatus));
        } elseif ($expectedStatus === false) {
            $this->assertNotEquals('2', floor($actualStatus / 100), sprintf($message, 'non-successful'));
        } else {
            $this->assertEquals($expectedStatus, $actualStatus, sprintf($message, $expectedStatus));
        }
    }

    private function getResponseStatusLine(Response $response): string {
        $fullResponse = (string)$response;
        return trim(strtok($fullResponse, "\n"));
    }
}
