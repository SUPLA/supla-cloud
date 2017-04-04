<?php
// SuplaBundle/Validator/Constraints/Channel.php
namespace SuplaBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Channel extends Constraint {
    public $config_message = 'Incorrect channel config!';
    public $class_message = 'Incorrect instance!';

    public function getTargets() {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy() {
        return 'channel_validator';
    }
}
