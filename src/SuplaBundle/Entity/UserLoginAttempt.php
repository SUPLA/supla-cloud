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

namespace SuplaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="SuplaBundle\Repository\UserLoginAttemptRepository")
 * @ORM\Table(name="supla_user_login_attempt", indexes={
 *     @ORM\Index(name="supla_user_login_attempt_ipv4_email_idx", columns={"email", "ipv4"}),
 *     @ORM\Index(name="supla_user_login_attempt_created_at_idx", columns={"created_at"})
 * })
 */
class UserLoginAttempt {
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="loginAttempts")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    private $user;

    /**
     * @ORM\Column(name="email", type="string", length=255)
     * @Groups({"basic"})
     */
    private $email;

    /**
     * @ORM\Column(name="created_at", type="utcdatetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(name="ipv4", type="integer", options={"unsigned"=true})
     */
    private $ipv4;

    /**
     * @ORM\Column(name="successful", type="boolean")
     */
    private $successful;

    public function __construct() {
    }

    public function getId(): int {
        return $this->id;
    }

    public function getEmail(): string {
        return $this->email;
    }

    /** @return User|null */
    public function getUser() {
        return $this->user;
    }

    public function getCreatedAt(): \DateTime {
        return $this->createdAt;
    }

    public function getSuccessful(): bool {
        return $this->successful;
    }
}
