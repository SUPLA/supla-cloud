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

namespace SuplaBundle\Entity\Main;

use Doctrine\ORM\Mapping as ORM;
use SuplaBundle\Entity\BelongsToUser;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="SuplaBundle\Repository\AmazonAlexaRepository")
 * @ORM\Table(name="supla_amazon_alexa")
 */
class AmazonAlexa {
    use BelongsToUser;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, unique=true, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\Column(name="reg_date", type="utcdatetime")
     */
    private $regDate;

    /**
     * @ORM\Column(name="access_token", type="string", length=1024, nullable=false)
     */
    private $accessToken;

    /**
     * @ORM\Column(name="expires_at", type="utcdatetime", nullable=false)
     */
    private $expiresAt;

    /**
     * @ORM\Column(name="refresh_token", type="string", length=1024, nullable=false)
     */
    private $refreshToken;

    /**
     * @ORM\Column(name="region", type="string", length=5, nullable=true)
     * @Groups({"basic"})
     */
    private $region;

    public function __construct(User $user) {
        $this->user = $user;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getUser(): User {
        return $this->user;
    }

    /**
     * @return \DateTime
     */
    public function getRegDate() {
        return $this->regDate;
    }

    /**
     * @param \DateTime $regDate
     */
    public function setRegDate($regDate) {
        $this->regDate = $regDate;
    }

    /**
     * @return string
     */
    public function getAccessToken() {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken($accessToken) {
        $this->accessToken = $accessToken;
    }

    /**
     * @param \DateTime $expiresAt
     */
    public function setExpiresAt($expiresAt) {
        $this->expiresAt = $expiresAt;
    }

    /**
     * @return \DateTime
     */
    public function getExpiresAt() {
        return $this->expiresAt;
    }

    /**
     * @return string
     */
    public function getRefreshToken() {
        return $this->refreshToken;
    }

    /**
     * @param string $refreshToken
     */
    public function setRefreshToken($refreshToken) {
        $this->refreshToken = $refreshToken;
    }

    /**
     * @return string
     */
    public function getRegion() {
        return $this->region;
    }

    /**
     * @param string $region
     */
    public function setRegion($region) {
        $this->region = $region;
    }
}
