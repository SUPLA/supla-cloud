<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.
 
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
