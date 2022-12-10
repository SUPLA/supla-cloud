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

use Assert\Assertion;
use Doctrine\ORM\Mapping as ORM;
use FOS\OAuthServerBundle\Entity\Client;
use SuplaBundle\Entity\BelongsToUser;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\ApiClientType;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="SuplaBundle\Repository\ApiClientRepository")
 * @ORM\Table(name="supla_oauth_clients", indexes={
 *     @ORM\Index(name="supla_oauth_clients_random_id_idx", columns={"random_id"}),
 *     @ORM\Index(name="supla_oauth_clients_type_idx", columns={"type"})
 * })
 */
class ApiClient extends Client {
    use BelongsToUser;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"basic"})
     */
    protected $id;

    /**
     * @ORM\Column(name="type", type="integer")
     */
    protected $type;

    /**
     * @ORM\ManyToOne(targetEntity="SuplaBundle\Entity\Main\User", inversedBy="apiClients")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     * @Groups({"basic"})
     */
    private $name;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Groups({"basic"})
     */
    private $description;

    /**
     * @ORM\Column(name="long_description", type="text", nullable=true)
     * @Groups({"basic"})
     */
    private $longDescription;

    /**
     * @ORM\Column(name="public_client_id", type="string", nullable=true)
     */
    private $publicClientId = null;

    public function __construct() {
        parent::__construct();
        $this->type = ApiClientType::ADMIN;
    }

    /**
     * @Groups({"basic"})
     */
    public function getGrantType(): string {
        return implode(', ', $this->getAllowedGrantTypes());
    }

    public function getType(): ApiClientType {
        return new ApiClientType($this->type);
    }

    public function setType(ApiClientType $type) {
        Assertion::eq($this->type, ApiClientType::ADMIN, 'You cannot change the type of an existing non-ADMIN type client.');
        $this->type = $type->getValue();
    }

    /** @Groups({"basic"}) */
    public function getPublicId() {
        return parent::getPublicId();
    }

    /** @Groups({"secret"}) */
    public function getSecret() {
        return parent::getSecret();
    }

    /** @Groups({"basic"}) */
    public function getRedirectUris() {
        return parent::getRedirectUris();
    }

    public function getName() {
        return $this->name;
    }

    public function setName(string $name) {
        $this->name = $name;
    }

    public function getDescription() {
        return @json_decode($this->description, true) ?: $this->description;
    }

    public function setDescription($description) {
        if (is_array($description)) {
            $description = json_encode($description);
        }
        $this->description = $description;
    }

    public function getLongDescription() {
        return @json_decode($this->longDescription, true) ?: $this->longDescription;
    }

    private function setLongDescription($longDescription) {
        if (is_array($longDescription)) {
            $longDescription = json_encode($longDescription);
        }
        $this->longDescription = $longDescription;
    }

    public function getUser() {
        return $this->user;
    }

    public function setUser(User $user) {
        $this->user = $user;
    }

    /** @return string|null */
    public function getPublicClientId() {
        return $this->publicClientId;
    }

    public function setPublicClientId($publicClientId) {
        $this->publicClientId = $publicClientId;
    }

    public function updateDataFromAutodiscover(array $clientData) {
        $this->setName($clientData['name']);
        $this->setDescription($clientData['description'] ?? '');
        $this->setLongDescription($clientData['longDescription'] ?? '');
        $this->setRedirectUris($clientData['redirectUris']);
    }
}
