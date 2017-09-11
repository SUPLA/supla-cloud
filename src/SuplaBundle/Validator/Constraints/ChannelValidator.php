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

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Model\IODeviceManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ChannelValidator extends ConstraintValidator {

    private $dev_miodevice_manager;

    public function __construct(IODeviceManager $iodevice_manager) {
        $this->iodevice_manager = $iodevice_manager;
    }

    public function validate($channel, Constraint $constraint) {
        $msg = null;

        if ($channel instanceof IODeviceChannel) {
            $f = $this->iodevice_manager->channelFunctionMap($channel->getType(), $channel->getFuncList());

            if (!is_array($f) || !in_array($channel->getFunction(), $f)) {
                $msg = $constraint->config_message;
            }
        } else {
            $msg = $constraint->class_message;
        }

        if ($msg !== null) {
            $this->context->buildViolation($msg)->addViolation();
        }
    }
}
