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

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use SuplaBundle\Auth\OAuthScope;
use SuplaBundle\Entity\BelongsToUser;
use SuplaBundle\Entity\Main\User;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="SuplaBundle\Repository\ApiClientAuthorizationRepository")
 * @ORM\Table(name="supla_oauth_client_authorizations",
 *     uniqueConstraints={@UniqueConstraint(name="UNIQUE_USER_CLIENT", columns={"user_id", "client_id"})})
 */
class ApiClientAuthorization {
    use BelongsToUser;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"basic"})
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="SuplaBundle\Entity\Main\User", inversedBy="apiClientAuthorizations")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="ApiClient")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @Groups({"client"})
     */
    private $apiClient;

    /**
     * @ORM\Column(name="scope", type="string", length=2000, nullable=false)
     * @Groups({"basic"})
     */
    private $scope;

    /**
     * @ORM\Column(name="mqtt_broker_auth_password", type="string", length=128, nullable=true)
     */
    private $mqttBrokerAuthPassword;

    /**
     * @ORM\Column(name="authorization_date", type="utcdatetime")
     * @Groups({"basic"})
     */
    private $authorizationDate;

    public function getId(): int {
        return $this->id;
    }

    public function getUser(): User {
        return $this->user;
    }

    public function setUser(User $user) {
        $this->user = $user;
    }

    public function getApiClient(): ApiClient {
        return $this->apiClient;
    }

    public function setApiClient(ApiClient $apiClient) {
        $this->apiClient = $apiClient;
    }

    public function getScope(): string {
        return (string)$this->scope;
    }

    public function setScope($scope) {
        $this->scope = (string)(new OAuthScope($scope))->ensureThatAllScopesAreSupported()->addImplicitScopes();
    }

    public function getAuthorizationDate(): DateTime {
        return $this->authorizationDate;
    }

    public function setAuthorizationDate(DateTime $authorizationDate) {
        $this->authorizationDate = $authorizationDate;
    }

    public function isAuthorized($scope): bool {
        return (new OAuthScope($this->scope))->hasAllScopes($scope);
    }

    public function authorizeNewScope($scope) {
        $this->setScope((new OAuthScope($this->scope))->merge($scope));
    }

    public function setMqttBrokerAuthPassword(string $mqttBrokerAuthPassword) {
        $this->mqttBrokerAuthPassword = $mqttBrokerAuthPassword;
    }
}
