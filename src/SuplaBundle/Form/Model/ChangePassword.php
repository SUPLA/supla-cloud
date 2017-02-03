<?php
/*
 src/SuplaBundle/Form/Model/ChangePassword.php

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

use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;
use SuplaBundle\Validator\Constraints as SuplaAssert;


class ChangePassword
{

	/**
	 * @SecurityAssert\UserPassword(
	 *     message = "Current password is incorrect"
	 * )
	 */
	protected $oldPassword;
	
	/**
	 * @Assert\Length(
	 *     min = 8, 
	 *     minMessage="The password should be 8 or more characters."
	 * )
	 * @Assert\NotBlank(message="Password field cannot be left empty")
	 */
	protected $newPassword;
	
	
	/**
     * @Assert\Expression(
     *     "this.getNewPassword() == this.getConfirmPassword()",
     *     message="The password and its confirm are not the same"
     * )
     */
	protected $confirmPassword;
	
	function getOldPassword()
    {
		return $this->oldPassword;
	}
	
	function setOldPassword($oldPassword) 
	{
		$this->oldPassword = $oldPassword;
	}
    
	function getNewPassword()
	{
		return $this->newPassword;
	}
	
	function setNewPassword($newPassword)
	{
		$this->newPassword = $newPassword;
	}
	
	function getConfirmPassword()
	{
		return $this->confirmPassword;
	}
	
	function setConfirmPassword($confirmPassword)
	{
		$this->confirmPassword = $confirmPassword;
	}
}
