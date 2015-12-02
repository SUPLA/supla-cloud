<?php 
// AppBundle/Validator/Constraints/ChannelValidator.php
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