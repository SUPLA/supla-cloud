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

namespace SuplaBundle\Auth;

use SuplaBundle\Exception\ApiException;

final class OAuthScope {
    const SCOPE_DELIMITER = ' ';

    /** @var array */
    private $scopes;

    public function __construct($scopes) {
        if (!is_array($scopes)) {
            $scopes = $this->scopeToArray($scopes);
        } elseif ($scopes instanceof self) {
            $this->scopes = $scopes->scopes;
        }
        $this->scopes = $scopes;
    }

    public function remove(string $scope): self {
        $this->scopes = array_filter($this->scopes, function ($existingScope) use ($scope) {
            return $existingScope !== $scope;
        });
        return $this;
    }

    public function removeImplicitScopes(): self {
        $this->scopes = array_filter($this->scopes, function (string $scope) {
            return !preg_match('#_r$#', $scope) || !$this->hasScope($scope . 'w', false);
        });
        return $this;
    }

    public function ensureThatAllScopesAreSupported(): self {
        $unsupportedScopes = array_diff($this->scopes, self::getSupportedScopes());
        if ($unsupportedScopes) {
            throw new ApiException('Unsupported scopes: ' . implode(', ', $unsupportedScopes));
        }
        return $this;
    }

    public function ensureThatAllScopesAreKnown(): self {
        $unsupportedScopes = array_diff($this->scopes, self::getAllKnownScopes());
        if ($unsupportedScopes) {
            throw new ApiException('Unknown scopes: ' . implode(', ', $unsupportedScopes));
        }
        return $this;
    }

    public function removeUnsupportedScopes(): self {
        $this->scopes = array_intersect(self::getSupportedScopes(), $this->scopes);
        return $this;
    }

    public function addImplicitScopes(): self {
        $rwScopes = array_filter(self::getSupportedScopes(), function (string $scope) {
            return preg_match('#_rw$#', $scope);
        });
        foreach ($rwScopes as $rwScope) {
            $rScope = substr($rwScope, 0, -1);
            if ($this->hasScope($rwScope, false) && !$this->hasScope($rScope, false)) {
                $this->scopes[] = $rScope;
            }
        }
        return $this;
    }

    public function hasScope(string $scope, bool $respectImplicit = true): bool {
        $scopes = new self($this);
        if ($respectImplicit) {
            $scopes->addImplicitScopes();
        }
        return in_array($scope, $scopes->scopes);
    }

    public function hasAllScopes($scopes): bool {
        $scopes = new self($scopes);
        foreach ($scopes->scopes as $scope) {
            if (!$this->hasScope($scope)) {
                return false;
            }
        }
        return true;
    }

    public function merge($scopes): self {
        $scopes = new self($scopes);
        $this->scopes = array_unique(array_merge($this->scopes, $scopes->scopes));
        return $this;
    }

    public function __toString() {
        return implode(self::SCOPE_DELIMITER, $this->scopes);
    }

    private function scopeToArray(string $scope): array {
        return array_values(array_filter(explode(self::SCOPE_DELIMITER, $scope)));
    }

    public static function getSupportedScopes(): array {
        return array_diff(self::getAllKnownScopes(), ['restapi']);
    }

    public static function getScopeForWebappToken(): self {
        return (new self(self::getSupportedScopes()))->remove('offline_access')->remove('state_webhook');
    }

    public static function getAllKnownScopes(): array {
        $supportedScopes = ['restapi', 'offline_access', 'channels_ea', 'channelgroups_ea', 'channels_files', 'state_webhook'];
        foreach ([
                     'accessids',
                     'account',
                     'channels',
                     'channelgroups',
                     'clientapps',
                     'directlinks',
                     'iodevices',
                     'locations',
                     'schedules',
                 ] as $rwScope) {
            $supportedScopes[] = $rwScope . '_r';
            $supportedScopes[] = $rwScope . '_rw';
        }
        return $supportedScopes;
    }
}
