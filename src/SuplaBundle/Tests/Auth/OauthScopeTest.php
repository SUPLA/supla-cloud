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

namespace SuplaBundle\Tests\Auth;

use PHPUnit\Framework\TestCase;
use SuplaBundle\Auth\OAuthScope;
use SuplaBundle\Exception\ApiException;

class OauthScopeTest extends TestCase {
    public function testGetSupportedScopes() {
        $scopes = OAuthScope::getSupportedScopes();
        $this->assertContains('offline_access', $scopes);
        $this->assertContains('iodevices_r', $scopes);
        $this->assertContains('locations_rw', $scopes);
        $this->assertContains('channels_ea', $scopes);
    }

    public function testToString() {
        $this->assertEquals('account_r account_rw offline_access', (string)(new OAuthScope('account_r account_rw offline_access')));
        $this->assertEquals('account_r offline_access', (string)(new OAuthScope(['account_r', 'offline_access'])));
    }

    public function testRemoveImplicitScopes() {
        $scope = (new OAuthScope('account_r account_rw offline_access'))->removeImplicitScopes();
        $this->assertEquals('account_rw offline_access', (string)$scope);
    }

    public function testRemoveUnsupportedScopes() {
        $scope = (new OAuthScope('account_r unicorn offline_access'))->removeUnsupportedScopes();
        $this->assertEquals('offline_access account_r', (string)$scope);
    }

    public function testEnsuringAllScopesAreSupported() {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('unicorn');
        (new OAuthScope('account_r unicorn offline_access'))->ensureThatAllScopesAreSupported();
    }

    public function testAddImplicitScopes() {
        $scope = (new OAuthScope('account_rw offline_access'))->addImplicitScopes();
        $this->assertEquals('account_rw offline_access account_r', (string)$scope);
        $scope = (new OAuthScope('account_rw locations_r'))->addImplicitScopes();
        $this->assertEquals('account_rw locations_r account_r', (string)$scope);
    }

    public function testMerging() {
        $scope = (new OAuthScope('account_rw offline_access'))->merge('account_r iodevices_r')->removeImplicitScopes();
        $this->assertEquals('account_rw offline_access iodevices_r', (string)$scope);
    }

    public function testHasScope() {
        $scope = new OAuthScope('account_rw');
        $this->assertTrue($scope->hasScope('account_rw'));
        $this->assertTrue($scope->hasScope('account_r'));
        $this->assertTrue($scope->hasScope('account_rw', false));
        $this->assertFalse($scope->hasScope('account_r', false));
    }

    public function testHasAllScopes() {
        $scope = new OAuthScope('account_rw locations_r channels_ea');
        $this->assertTrue($scope->hasAllScopes(''));
        $this->assertTrue($scope->hasAllScopes('locations_r'));
        $this->assertTrue($scope->hasAllScopes('account_rw locations_r'));
        $this->assertTrue($scope->hasAllScopes('account_r locations_r'));
        $this->assertTrue($scope->hasAllScopes('account_r channels_ea'));
        $this->assertFalse($scope->hasAllScopes('account_r channels_ea offline_access'));
        $this->assertFalse($scope->hasAllScopes('account_r locations_rw'));
        $this->assertFalse($scope->hasAllScopes('locations_rw'));
    }

    public function testRemovingOneScope() {
        $scope = (new OAuthScope('account_rw offline_access'))->remove('offline_access');
        $this->assertEquals('account_rw', (string)$scope);
    }

    public function testIsEmptyScope() {
        $this->assertTrue((new OAuthScope(''))->isEmpty());
        $this->assertFalse((new OAuthScope('a'))->isEmpty());
    }
}
