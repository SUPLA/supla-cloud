<?php
// SuplaBundle/Validator/Constraints/Channel.php
namespace SuplaBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UsernameExists extends Constraint {
    public $message = 'User %string% does not exist';

    public function validatedBy() {
        return 'username_exists_validator';
    }
}
