<?php
namespace SuplaBundle\Tests\Integration;

class IntegrationTestCaseListener extends \PHPUnit_Framework_BaseTestListener {
    public function startTest(\PHPUnit_Framework_Test $test) {
        if ($test instanceof IntegrationTestCase) {
            $test->prepareIntegrationTest();
        }
    }
}
