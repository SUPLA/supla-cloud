<?php
/*
 src/SuplaBundle/Form/Model/ForgotPassword.php

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

use SuplaBundle\Validator\Constraints as SuplaAssert;
use Google\RecaptchaBundle\Validator\Constraints as Recaptcha;

class ForgotPassword
{
	/**
	 * @SuplaAssert\UsernameExists
	 */
    protected $email;
    
    /**    
    * @Recaptcha\True
    */
    protected $recaptcha;

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getEmail()
    {
        return $this->email;
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
