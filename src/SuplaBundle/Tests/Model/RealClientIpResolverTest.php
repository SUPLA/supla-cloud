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

namespace SuplaBundle\Tests\Model;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SuplaBundle\Model\RealClientIpResolver;
use SuplaBundle\Supla\SuplaAutodiscover;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RealClientIpResolverTest extends TestCase {
    /** @var RequestStack|MockObject */
    private $requestStack;
    /** @var SuplaAutodiscover|MockObject */
    private $autodiscover;
    /** @var RealClientIpResolver */
    private $resolver;
    /** @var Request|MockObject */
    private $requestMock;

    /** @before */
    public function init() {
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->autodiscover = $this->createMock(SuplaAutodiscover::class);
        $this->resolver = new RealClientIpResolver($this->requestStack, $this->autodiscover);
        $this->requestMock = $this->createMock(Request::class);
        $this->requestMock->method('getClientIp')->willReturn('1.2.3.4');
        $this->requestMock->headers = new ParameterBag();
    }

    public function testWhenNoRequest() {
        $ip = $this->resolver->getRealIp();
        $this->assertNull($ip);
    }

    public function testWhenOnlyRequestIp() {
        $this->requestStack->method('getCurrentRequest')->willReturn($this->requestMock);
        $ip = $this->resolver->getRealIp();
        $this->assertEquals('1.2.3.4', $ip);
    }

    public function testOriginalIpWhenOverriddenByNonBroker() {
        $this->requestStack->method('getCurrentRequest')->willReturn($this->requestMock);
        $this->requestMock->headers->add(['X-REAL-IP' => '2.3.4.5']);
        $ip = $this->resolver->getRealIp();
        $this->assertEquals('1.2.3.4', $ip);
    }

    public function testRealIpWhenOverriddenByBroker() {
        $this->requestStack->method('getCurrentRequest')->willReturn($this->requestMock);
        $this->autodiscover->method('getBrokerClouds')->willReturn([['ips' => ['5.6.7.8', '1.2.3.4']], ['ips' => ['3.3.3.3']]]);
        $this->requestMock->headers->add(['X-REAL-IP' => '2.3.4.5']);
        $ip = $this->resolver->getRealIp();
        $this->assertEquals('2.3.4.5', $ip);
    }
}
