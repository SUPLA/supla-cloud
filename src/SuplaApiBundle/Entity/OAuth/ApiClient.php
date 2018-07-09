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

namespace SuplaApiBundle\Entity\OAuth;

use Assert\Assertion;
use Doctrine\ORM\Mapping as ORM;
use FOS\OAuthServerBundle\Entity\Client;
use SuplaApiBundle\Enums\ApiClientType;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="SuplaBundle\Repository\ApiClientRepository")
 * @ORM\Table(name="supla_oauth_clients")
 */
class ApiClient extends Client {
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

    /** @Groups({"basic"}) */
    public function getSecret() {
        return parent::getSecret();
    }
}
