<?php 
// AppBundle/Validator/Constraints/Channel.php
namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author Przemyslaw Zygmunt AC SOFTWARE SP. Z O.O. <p.zygmunt@acsoftware.pl>
 * @Annotation
 */
class UsernameExists extends Constraint
{
    public $message = 'User %string% does not exist';

    
    public function validatedBy()
    {
    	return 'username_exists_validator';
    }
   
}
?>