<?php 
/*
 src/AppBundle/Validator/Constraints/ChannelValidator.php

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

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use AppBundle\Model\IODeviceManager;
use AppBundle\Entity\IODeviceChannel;

/**
 * @author Przemyslaw Zygmunt AC SOFTWARE SP. Z O.O. <p.zygmunt@acsoftware.pl>
 */
class ChannelValidator extends ConstraintValidator
{		
	
	private $dev_miodevice_manager;
	
	public function __construct(IODeviceManager $iodevice_manager)
	{
		$this->iodevice_manager = $iodevice_manager;
	}
	
	public function validate($channel, Constraint $constraint)
	{
		    $msg = null;
		    
		    if ( $channel instanceof IODeviceChannel ) {
		    	
		    	$fmap = $this->iodevice_manager->channelFunctionMap();
		    	$f = @$fmap[$channel->getType()];
		    	
		    	if ( !is_array($f) || !in_array($channel->getFunction(), $f) ) {
		    		$msg = $constraint->config_message;
		    	}
		    	
		    } else {
		    	$msg = $constraint->class_message;
		    }
		    
		    if ( $msg !== null )
		    	$this->context->buildViolation($msg)->addViolation();
			
	}
	
}
?>