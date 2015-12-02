<?php
/*
 src/AppBundle/Form/Type/RegistrationFormType.php

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

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author Przemyslaw Zygmunt p.zygmunt@acsoftware.pl [AC SOFTWARE]
 */
class RegistrationType extends AbstractType
{
	
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	
        $builder->add('user', new UserType());
        $builder->add('recaptcha', 'ewz_recaptcha',
        		array(  'label' => ' ', 'language' => $options['language'],
        				'attr' => array(
        						'options' => array(
        								'theme' => 'clean',
        								'type'  => 'image',
        						)
        				)
        		));
        $builder->add('Create', 'submit', array('label' => 'Create an account',
    			'attr' => array('class' => 'btn btn-default')
    	));
        
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    	$resolver->setDefaults(array(
    			'language' => 'en'
    	));
    }

    public function getName()
    {
        return '_user_registration_type';
    }
    
}