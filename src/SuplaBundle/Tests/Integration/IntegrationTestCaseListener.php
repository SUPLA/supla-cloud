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

namespace SuplaBundle\Tests\Integration;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;

class IntegrationTestCaseListener implements TestListener {
    use TestListenerDefaultImplementation;

    public function startTest(Test $test): void {
        defined('APPLICATION_ENV') || define('APPLICATION_ENV', 'test');
        if ($test instanceof IntegrationTestCase) {
            try {
                $test->prepareIntegrationTest();
            } catch (\Exception $e) {
                throw new ExpectationFailedException('Could not start the test: ' . $e->getMessage(), null, $e);
            }
        }
    }
}
