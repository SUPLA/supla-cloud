<?php
/*
 src/SuplaBundle/Form/Model/Registration.php

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

namespace SuplaBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;
use SuplaBundle\Entity\User;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints as Recaptcha;

class Registration
{
    /**
     * @Assert\Type(type="SuplaBundle\Entity\User")
     * @Assert\Valid()
     */
    protected $user;
    
    /**    
    * @Recaptcha\IsTrue
    */
    protected $recaptcha;

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }
    
    public function getRecaptcha()
    {
    	return $this->recaptcha;
    }
    
    public function setRecaptcha($recaptcha)
    {
    	$this->recaptcha = $recaptcha;
    }
    

}
