<?php
// SuplaBundle/Validator/Constraints/ChannelValidator.php
namespace SuplaBundle\Validator\Constraints;

use SuplaBundle\Entity\User;
use SuplaBundle\Model\UserManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UsernameExistsValidator extends ConstraintValidator {

    private $user_manager;

    public function __construct(UserManager $user_manager) {
        $this->user_manager = $user_manager;
    }

    public function validate($email, Constraint $constraint) {
        if (!($this->user_manager->userByEmail($email) instanceof User)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%string%', $email)
                ->addViolation();
        }
    }
}
