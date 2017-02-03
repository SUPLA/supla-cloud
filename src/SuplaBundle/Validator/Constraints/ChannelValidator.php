<?php 
// SuplaBundle/Validator/Constraints/ChannelValidator.php
namespace SuplaBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use SuplaBundle\Model\IODeviceManager;
use SuplaBundle\Entity\IODeviceChannel;

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
		    	
		    	$f = $this->iodevice_manager->channelFunctionMap($channel->getType(), $channel->getFuncList());
		    	
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