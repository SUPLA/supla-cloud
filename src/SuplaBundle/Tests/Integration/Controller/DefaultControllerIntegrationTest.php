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

namespace SuplaBundle\Tests\Integration\Controller;

use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class DefaultControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    public function testGettingApiDocs() {
        self::ensureKernelShutdown();
        $client = self::createClient();
        $client->request('GET', '/api-docs/supla-api-docs.yaml');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = $response->getContent();
        $this->assertStringContainsString('SUPLA Cloud API', $content);
        $this->assertStringContainsString('supla.local', $content);
        $this->assertStringContainsString('components/schemas/AccessIdentifier', $content);
    }

    public function testGettingApiDocsV24() {
        self::ensureKernelShutdown();
        $client = self::createClient();
        $client->request('GET', '/api-docs/supla-api-docs-v3.yaml');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = $response->getContent();
        $this->assertStringContainsString('SUPLA Cloud API', $content);
        $this->assertStringContainsString('supla.local', $content);
        $this->assertStringContainsString('components/schemas/AccessIdentifier', $content);
    }
}
