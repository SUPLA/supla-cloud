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

namespace SuplaBundle\Entity\Main\OAuth;

use Doctrine\ORM\Mapping as ORM;
use FOS\OAuthServerBundle\Entity\RefreshToken as BaseRefreshToken;
use SuplaBundle\Auth\OAuthScope;

/**
 * @ORM\Entity
 * @ORM\Table(name="supla_oauth_refresh_tokens")
 * @ORM\AttributeOverrides({@ORM\AttributeOverride(name="scope", column=@ORM\Column(length=2000))})
 */
class RefreshToken extends BaseRefreshToken {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="ApiClient")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $client;

    /**
     * @ORM\ManyToOne(targetEntity="SuplaBundle\Entity\Main\User")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="SuplaBundle\Entity\Main\OAuth\ApiClientAuthorization")
     * @ORM\JoinColumn(name="api_client_authorization_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $apiClientAuthorization;

    public function setScope($scope) {
        parent::setScope((string)(new OAuthScope($scope))->ensureThatAllScopesAreKnown()->addImplicitScopes());
    }

    public function setApiClientAuthorization(ApiClientAuthorization $apiClientAuthorization) {
        $this->apiClientAuthorization = $apiClientAuthorization;
    }

    public function expire(): void {
        $this->expiresAt = time() - 3600;
    }
}
