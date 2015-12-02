<?php 
// AppBundle/Validator/Constraints/ChannelValidator.php
namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use AppBundle\Model\UserManager;
use AppBundle\Entity\User;

/**
 * @author Przemyslaw Zygmunt AC SOFTWARE SP. Z O.O. <p.zygmunt@acsoftware.pl>
 */
class UsernameExistsValidator extends ConstraintValidator
{		
	
	private $user_manager;
	
	public function __construct($user_manager)
	{
		$this->user_manager = $user_manager;
	}
	
	public function validate($email, Constraint $constraint)
	{
		if ( !($this->user_manager->userByEmail($email) instanceof User) ) {
			$this->context->buildViolation($constraint->message)
			->setParameter('%string%', $email)
			->addViolation();
		}
			
	}
	
}
?>