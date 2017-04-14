<?php
/*
 src/SuplaBundle/Form/Type/UserType.php

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

namespace SuplaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('email', EmailType::class, ['label' => ' ',
            'required' => true]);
        $builder->add('plainPassword', RepeatedType::class, [
            'required' => true,
            'first_name' => 'password',
            'second_name' => 'confirm',
            'type' => PasswordType::class,
            'first_options' => ['label' => ' '],
            'second_options' => ['label' => ' '],
            'invalid_message' => 'The password and its confirm are not the same.',
        ]);
        $builder->add('timezone', HiddenType::class, ['label' => false, 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'SuplaBundle\Entity\User',
        ]);
    }

    public function getBlockPrefix() {
        return '_user_type';
    }
}
