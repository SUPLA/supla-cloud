<?php 
// AppBundle/Validator/Constraints/Channel.php
namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author Przemyslaw Zygmunt AC SOFTWARE SP. Z O.O. <p.zygmunt@acsoftware.pl>
 * @Annotation
 */
class Channel extends Constraint
{
    public $config_message = 'Incorrect channel config!';
    public $class_message = 'Incorrect instance!';
    
    public function getTargets()
    {
    	return self::CLASS_CONSTRAINT;
    }
    
    public function validatedBy()
    {
    	return 'channel_validator';
    }
   
}
?>