<?php
/*
 src/SuplaBundle/Form/Type/RegistrationFormType.php

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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class RegistrationType extends AbstractType
{
	
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	
        $builder->add('user', UserType::class);
        $builder->add('recaptcha', EWZRecaptchaType::class,
        		array(  'label' => ' ', 
        				'language' => $options['language'],
        				'attr' => array(
        						'options' => array(
        								'theme' => 'clean',
        								'type'  => 'image',
        								'size'  => 'normal',
        						)
        				)
        		));
        $builder->add('Create', SubmitType::class, array('label' => 'Create an account',
    			'attr' => array('class' => 'btn btn-default')
    	));
        
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
    	$resolver->setDefaults(array(
    			'language' => 'en'
    	));
    }

    public function getBlockPrefix()
    {
        return '_user_registration_type';
    }
    
    
}