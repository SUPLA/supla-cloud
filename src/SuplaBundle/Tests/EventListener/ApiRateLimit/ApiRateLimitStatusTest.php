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

namespace SuplaBundle\Tests\EventListener\ApiRateLimit;

use PHPUnit\Framework\TestCase;
use SuplaBundle\EventListener\ApiRateLimit\ApiRateLimitRule;
use SuplaBundle\EventListener\ApiRateLimit\ApiRateLimitStatus;
use SuplaBundle\Tests\Integration\Traits\TestTimeProvider;

class ApiRateLimitStatusTest extends TestCase {
    /** @afterClass */
    public static function resetTimeProvider() {
        TestTimeProvider::reset();
    }

    public function testFromApiRateLimitRule() {
        $rule = new ApiRateLimitRule('100/3600');
        $timeProvider = new TestTimeProvider();
        TestTimeProvider::setTime(100);
        $status = ApiRateLimitStatus::fromRule($rule, $timeProvider);
        $this->assertEquals(100, $status->getRemaining());
        $this->assertEquals(100, $status->getLimit());
        $this->assertEquals(3700, $status->getReset());
        return $status;
    }

    /** @depends testFromApiRateLimitRule */
    public function testIsExpired(ApiRateLimitStatus $status) {
        $testTimeProvider = new TestTimeProvider();
        $this->assertFalse($status->isExpired($testTimeProvider));
        TestTimeProvider::setTime(3600);
        $this->assertFalse($status->isExpired($testTimeProvider));
        TestTimeProvider::setTime(3700);
        $this->assertFalse($status->isExpired($testTimeProvider));
        TestTimeProvider::setTime(3701);
        $this->assertTrue($status->isExpired($testTimeProvider));
    }

    /** @depends testFromApiRateLimitRule */
    public function testDecrement(ApiRateLimitStatus $status) {
        $status->decrement();
        $this->assertEquals(99, $status->getRemaining());
        $this->assertEquals(100, $status->getLimit());
        return $status;
    }

    /** @depends testDecrement */
    public function testToString(ApiRateLimitStatus $status) {
        $this->assertEquals('100,99,3700', $status->toString());
    }

    /** @depends testDecrement */
    public function testFromString(ApiRateLimitStatus $status) {
        $this->assertTrue($status == new ApiRateLimitStatus('100,99,3700'));
    }

    public function testExceeded() {
        $this->assertFalse((new ApiRateLimitStatus('100,99,10'))->isExceeded());
        $this->assertFalse((new ApiRateLimitStatus('0,99,10'))->isExceeded());
        $this->assertFalse((new ApiRateLimitStatus('10,99,0'))->isExceeded());
        $this->assertFalse((new ApiRateLimitStatus('10,1,10'))->isExceeded());
        $this->assertFalse((new ApiRateLimitStatus('10,0,10'))->isExceeded());
        $this->assertTrue((new ApiRateLimitStatus('10,-1,10'))->isExceeded());
        $this->assertTrue((new ApiRateLimitStatus('10,-100,10'))->isExceeded());
    }

    public function getRemaining() {
        $this->assertEquals(99, (new ApiRateLimitStatus('100,99,10'))->getRemaining());
        $this->assertEquals(0, (new ApiRateLimitStatus('100,0,10'))->getRemaining());
        $this->assertEquals(0, (new ApiRateLimitStatus('100,-100,10'))->getRemaining());
    }

    public function testDecrementingAndExcess() {
        $status = new ApiRateLimitStatus('100,3,10');
        $this->assertEquals(0, $status->getExcess());
        $this->assertEquals(3, $status->getRemaining());
        $this->assertFalse($status->isExceeded());
        $status->decrement();
        $status->decrement();
        $status->decrement();
        $this->assertEquals(0, $status->getExcess());
        $this->assertEquals(0, $status->getRemaining());
        $this->assertFalse($status->isExceeded());
        $status->decrement();
        $status->decrement();
        $this->assertEquals(2, $status->getExcess());
        $this->assertEquals(0, $status->getRemaining());
        $this->assertTrue($status->isExceeded());
    }
}
