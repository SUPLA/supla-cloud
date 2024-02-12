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
use Doctrine\ORM\Mapping\UniqueConstraint;
use SuplaBundle\Entity\BelongsToUser;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity(repositoryClass="SuplaBundle\Repository\ClientAppRepository")
 * @ORM\Table(name="supla_client", uniqueConstraints={@UniqueConstraint(name="UNIQUE_CLIENTAPP", columns={"user_id", "guid"})})
 */
class ClientApp {
    use BelongsToUser;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"basic"})
     */
    private $id;

    /**
     * @ORM\Column(name="guid", type="binary", length=16, nullable=false, unique=false)
     */
    private $guid;

    /**
     * @ORM\ManyToOne(targetEntity="AccessID", inversedBy="clientApps")
     * @ORM\JoinColumn(name="access_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * @Groups({"clientApp.accessId"})
     * @MaxDepth(1)
     */
    private $accessId;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="clientApps")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(name="name", type="string", length=100, nullable=true, options={"charset"="utf8mb4", "collation"="utf8mb4_unicode_ci"})
     * @Groups({"basic"})
     */
    private $name;

    /**
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     * @Groups({"basic"})
     */
    private $enabled = false;

    /**
     * @ORM\Column(name="reg_ipv4", type="ipaddress", nullable=true, options={"unsigned"=true})
     * @Groups({"basic"})
     */
    private $regIpv4;

    /**
     * @ORM\Column(name="reg_date", type="utcdatetime")
     * @Groups({"basic"})
     */
    private $regDate;

    /**
     * @ORM\Column(name="last_access_ipv4", type="ipaddress", nullable=true, options={"unsigned"=true})
     * @Groups({"basic"})
     */
    private $lastAccessIpv4;

    /**
     * @ORM\Column(name="last_access_date", type="utcdatetime")
     * @Groups({"basic"})
     */
    private $lastAccessDate;

    /**
     * @ORM\Column(name="software_version", type="string", length=20, nullable=false)
     * @Groups({"basic"})
     */
    private $softwareVersion;

    /**
     * @ORM\Column(name="protocol_version", type="integer", nullable=false)
     * @Groups({"basic"})
     */
    private $protocolVersion;

    /**
     * @ORM\Column(name="auth_key", type="string", length=64, nullable=true)
     */
    private $authKey;

    /**
     * @ORM\Column(name="caption", type="string", length=100, nullable=true, options={"charset"="utf8mb4", "collation"="utf8mb4_unicode_ci"})
     * @Groups({"basic"})
     */
    private $caption;

    /**
     * @ORM\Column(name="disable_after_date", type="utcdatetime", nullable=true)
     */
    private $disableAfterDate;

    /** @ORM\Column(name="push_token", type="string", length=255, nullable=true) */
    private $pushToken;

    /** @ORM\Column(name="push_token_update_time", type="datetime", nullable=true) */
    private $pushTokenUpdateTime;

    /** @ORM\Column(name="platform", type="tinyint", nullable=true, options={"unsigned": true}) */
    private $platform;

    /** @ORM\Column(name="app_id", type="integer", nullable=false, options={"default": 0}) */
    private $appId = 0;

    /** @ORM\Column(name="devel_env", type="boolean", options={"default": 0}) */
    private $isDevEnv = false;

    /** @ORM\Column(name="profile_name", type="string", length=50, nullable=true, options={"charset"="utf8mb4", "collation"="utf8mb4_unicode_ci"}) */
    private $profileName;

    public function getId(): int {
        return $this->id;
    }

    public function getAccessId(): ?AccessID {
        return $this->accessId;
    }

    public function setAccessId(?AccessID $accessId) {
        $this->accessId = $accessId;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name) {
        $this->name = $name;
    }

    public function getEnabled(): bool {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled) {
        $this->enabled = $enabled;
    }

    /** @return null|string */
    public function getRegIpv4() {
        return $this->regIpv4;
    }

    public function getRegDate() {
        return $this->regDate;
    }

    /** @return null|string */
    public function getLastAccessIpv4() {
        return $this->lastAccessIpv4;
    }

    public function getLastAccessDate() {
        return $this->lastAccessDate;
    }

    public function getSoftwareVersion() {
        return $this->softwareVersion;
    }

    public function getProtocolVersion() {
        return $this->protocolVersion;
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getCaption(): string {
        return empty($this->caption) ? $this->getName() : $this->caption;
    }

    public function setCaption(string $caption) {
        $this->caption = $caption;
    }

    public function getDisableAfterDate() {
        return $this->disableAfterDate;
    }
}
