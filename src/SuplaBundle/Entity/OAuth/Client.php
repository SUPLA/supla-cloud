<?php
/*
 src/SuplaBundle/Entity/OAuth/Client.php

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

use FOS\OAuthServerBundle\Entity\Client as BaseClient;
use Doctrine\ORM\Mapping as ORM;
use SuplaBundle\Entity\OAuth\User as OAuthUser;
use SuplaBundle\Entity\User as ParentUser;

/**
 * @ORM\Entity
 * @ORM\Table(name="supla_oauth_clients")
 */
class Client extends BaseClient
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(name="type", type="integer")
     */
    protected $type;
    
    /**
     * @ORM\ManyToOne(targetEntity="SuplaBundle\Entity\User")
     */
    protected $parent;
    
    private function pwdGen() {
    	return base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
    }
    
    public function __construct()
    {
    	parent::__construct();
        $this->type = 0;
    }
        
    public function getGrantType() {
    	return implode(', ', $this->getAllowedGrantTypes());
    }
    
    public function setType($type) {
    	$this->type = $type;
    }
    
    public function getType() {
    	return $this->type;
    }
    
    public function getParentUser() {
    	return $this->parent;
    }
    
    public function setParent(ParentUser $parent) {
    	$this->parent = $parent;
    }
    
}