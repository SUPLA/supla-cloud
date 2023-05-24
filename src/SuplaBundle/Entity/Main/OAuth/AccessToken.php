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
use FOS\OAuthServerBundle\Entity\AccessToken as BaseAccessToken;
use SuplaBundle\Auth\OAuthScope;
use SuplaBundle\Entity\BelongsToUser;
use SuplaBundle\Entity\Main\AccessID;
use SuplaBundle\Enums\ApiClientType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="SuplaBundle\Repository\AccessTokenRepository")
 * @ORM\Table(name="supla_oauth_access_tokens")
 * @ORM\AttributeOverrides({@ORM\AttributeOverride(name="scope", column=@ORM\Column(length=2000))})
 */
class AccessToken extends BaseAccessToken {
    use BelongsToUser;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"basic"})
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="ApiClient")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    protected $client;

    /**
     * @ORM\ManyToOne(targetEntity="SuplaBundle\Entity\Main\User")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="RefreshToken")
     * @ORM\JoinColumn(name="issued_with_refresh_token_id", nullable=true, onDelete="SET NULL")
     */
    protected $issuedWithRefreshToken;

    /**
     * @ORM\Column(name="name", type="string", length=100, nullable=true)
     * @Groups({"basic"})
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="SuplaBundle\Entity\Main\AccessID")
     * @ORM\JoinColumn(name="access_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $accessId;

    /**
     * @ORM\ManyToOne(targetEntity="SuplaBundle\Entity\Main\OAuth\ApiClientAuthorization")
     * @ORM\JoinColumn(name="api_client_authorization_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $apiClientAuthorization;

    /**
     * @ORM\Column(name="issuer_ip", type="ipaddress", nullable=true, options={"unsigned"=true})
     */
    private $issuerIp;

    /**
     * @ORM\Column(name="issuer_browser_string", type="string", length=255, nullable=true)
     */
    private $issuerBrowserString;

    public function __construct(Request $request = null) {
        if ($request) {
            $this->issuerIp = $request->getClientIp();
            $this->issuerBrowserString = $request->headers->get('User-Agent');
        }
    }

    /** @Groups({"basic"}) */
    public function getScope() {
        return parent::getScope();
    }

    /** @Groups({"token"}) */
    public function getToken() {
        return parent::getToken();
    }

    public function getName() {
        return $this->name;
    }

    public function setName(string $name) {
        $this->name = $name;
    }

    public function setScope($scope) {
        parent::setScope((string)(new OAuthScope($scope))->ensureThatAllScopesAreKnown()->addImplicitScopes());
    }

    public function isPersonal(): bool {
        return !$this->getExpiresAt();
    }

    public function isForWebapp(): bool {
        return $this->client && $this->client->getType()->getValue() === ApiClientType::WEBAPP;
    }

    public function isForPublicApp(): bool {
        return $this->client && $this->client->getType()->getValue() === ApiClientType::BROKER;
    }

    /** @return AccessID|null */
    public function getAccessId() {
        return $this->accessId;
    }

    public function getApiClientAuthorization(): ?ApiClientAuthorization {
        return $this->apiClientAuthorization;
    }

    public function setApiClientAuthorization(ApiClientAuthorization $apiClientAuthorization) {
        $this->apiClientAuthorization = $apiClientAuthorization;
    }

    public function getIssuedWithRefreshToken(): ?RefreshToken {
        return $this->issuedWithRefreshToken;
    }

    public function setIssuedWithRefreshToken(?RefreshToken $refreshToken): void {
        $this->issuedWithRefreshToken = $refreshToken;
    }

    public function getIssuerIp(): ?string {
        return $this->issuerIp;
    }

    public function getIssuerBrowserString(): ?string {
        return $this->issuerBrowserString;
    }
}
