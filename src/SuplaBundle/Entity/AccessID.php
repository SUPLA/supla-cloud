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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="supla_accessid")
 */
class AccessID {
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"basic"})
     */
    private $id;

    /**
     * @ORM\Column(name="password", type="string", length=32, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Length(min=8, max=32)
     */
    private $password;

    /**
     * @ORM\Column(name="caption", type="string", length=100, nullable=true)
     * @Assert\NotBlank()
     * @Assert\Length(min=1, max=100)
     * @Groups({"basic"})
     */
    private $caption;

    /**
     * @ORM\Column(name="enabled", type="boolean",  nullable=false)
     * @Groups({"basic"})
     */
    private $enabled;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="accessids")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToMany(targetEntity="Location", mappedBy="accessIds", cascade={"persist"})
     */
    private $locations;

    /**
     * @ORM\OneToMany(targetEntity="ClientApp", mappedBy="accessId")
     **/
    private $clientApps;

    public function __construct(User $user = null) {

        $this->enabled = true;
        $this->clientApps = new ArrayCollection();
        $this->locations = new ArrayCollection();

        if ($user !== null) {
            $user->getAccessIDS()->add($this);
            $this->user = $user;
        }
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function setCaption($caption) {
        $this->caption = $caption;
    }

    public function getCaption() {
        return $this->caption;
    }

    public function getUser() {
        return $this->user;
    }

    public function getLocations() {
        return $this->locations;
    }

    public function getId() {
        return $this->id;
    }

    public function getEnabled() {
        return $this->enabled;
    }

    public function setEnabled($enabled) {
        $this->enabled = $enabled;
    }
}
