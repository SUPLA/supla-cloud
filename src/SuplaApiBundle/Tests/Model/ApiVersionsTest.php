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

namespace SuplaApiBundle\Tests\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use SuplaApiBundle\Model\ApiVersions;
use SuplaBundle\Controller\ClientAppController;
use SuplaBundle\Entity\ClientApp;
use SuplaBundle\Entity\User;
use SuplaBundle\Supla\SuplaServer;
use Symfony\Component\HttpFoundation\Request;

class ApiVersionsTest extends \PHPUnit_Framework_TestCase {
    public function testExtractingVersionFromRequest() {
        $this->assertEquals(ApiVersions::V2_2(), ApiVersions::fromRequest($this->requestMock('2.2')));
    }

    public function testDefaultVersionIfParam() {
        $this->assertEquals(ApiVersions::DEFAULT(), ApiVersions::fromRequest($this->requestMock('')));
    }

    public function testExceptionIfInvalidVersion() {
        $this->expectExceptionMessage('Invalid API version');
        $this->expectExceptionMessage('ala');
        $this->expectExceptionMessage('2.2.0');
        ApiVersions::fromRequest($this->requestMock('ala'));
    }

    public function testIsRequestedEqualOrGreaterThan() {
        $this->assertTrue(ApiVersions::V2_1()->isRequestedEqualOrGreaterThan($this->requestMock('2.2')));
        $this->assertTrue(ApiVersions::V2_1()->isRequestedEqualOrGreaterThan($this->requestMock('2.1')));
        $this->assertFalse(ApiVersions::V2_1()->isRequestedEqualOrGreaterThan($this->requestMock('2.0')));
    }

    public function testObtainingVersionFromString() {
        $this->assertEquals(ApiVersions::V2_2(), ApiVersions::fromString('2.2'));
        $this->assertEquals(ApiVersions::V2_2(), ApiVersions::fromString('v2.2'));
        $this->assertEquals(ApiVersions::V2_2(), ApiVersions::fromString('2.2.0'));
        $this->assertEquals(ApiVersions::V2_2(), ApiVersions::fromString('v2.2.0'));
    }

    private function requestMock(string $version): Request {
        $request = $this->createMock(Request::class);
        $request->method('get')->with('version')->willReturn($version);
        return $request;
    }
}
