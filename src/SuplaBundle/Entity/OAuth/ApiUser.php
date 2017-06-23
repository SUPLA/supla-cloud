<?php
/*
 src/SuplaBundle/Entity/OAuth/User.php

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

namespace SuplaBundle\Entity\OAuth;

use Doctrine\ORM\Mapping as ORM;
use SuplaBundle\Entity\AccessID;
use SuplaBundle\Entity\User as ParentUser;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="supla_oauth_user")
 */
class ApiUser implements AdvancedUserInterface {
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="SuplaBundle\Entity\User")
     */
    protected $parent;

    /**
     * @ORM\Column(name="password", type="string", length=64)
     */
    protected $password;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     * @Assert\NotBlank()
     * @Assert\Length(min=8)
     */
    protected $plainPassword;

    /**
     * @ORM\Column(name="enabled", type="boolean")
     * @Assert\NotNull()
     */
    protected $enabled;

    /**
     * @ORM\ManyToOne(targetEntity="SuplaBundle\Entity\AccessID")
     */
    protected $accessId;

    private function pwdGen() {
        return base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
    }

    public function __construct(ParentUser $parent) {
        $this->enabled = false;
        $this->parent = $parent;
        $this->password = $this->pwdGen();
    }

    public function getId() {
        return $this->id;
    }

    public function getParentUser() {
        return $this->parent;
    }

    public function getUsername() {
        return 'api_' . $this->id;
    }

    public function getSalt() {
        return $this->parent instanceof ParentUser ? $this->parent->getSalt() : null;
    }

    public function getPassword() {
        return $this->isEnabled() ? $this->password : $this->pwdGen();
    }

    public function setPassword($password) {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword() {
        return $this->plainPassword;
    }

    public function setPlainPassword($password) {
        $this->plainPassword = $password;
        return $this;
    }

    public function eraseCredentials() {
        $this->plainPassword = null;
    }

    /**
     * Returns the user roles
     *
     * @return array The roles
     */
    public function getRoles() {
        $roles[] = 'RESTAPI_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles) {
        $this->roles = [];

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    public function isEnabled() {
        return $this->parent instanceof ParentUser ? $this->enabled : false;
    }

    public function setEnabled($enabled) {
        $this->enabled = $enabled;
    }

    public function isAccountNonExpired() {
        return true;
    }

    public function isAccountNonLocked() {
        return true;
    }

    public function isCredentialsNonExpired() {
        return true;
    }

    public function getAccessId() {
        return $this->accessId;
    }

    public function setAccessId(AccessID $accessId) {
        $this->accessId = $accessId;
    }
}
