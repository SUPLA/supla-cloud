<?php 
/*
 src/SuplaBundle/Form/Type/IODeviceType.php

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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class IODeviceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('comment', TextareaType::class, array('label' => 'Comment', 'required' => false))
    	->add('enabled', CheckboxType::class, array('label' => 'Enabled', 'required' => false))
    	->add('cancel', ButtonType::class, array(
    			'label' => 'Cancel',
    			'attr' => array('class' => 'btn btn-default', 'onClick' => "location.href='".$options['cancel_url']."'"),
    	))
    	->add('save', SubmitType::class, array('label' => 'Save',
    			'attr' => array('class' => 'btn btn-default')
    	));
    }

    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SuplaBundle\Entity\IODevice',
        	'cancel_url' => ''
        ));
    }

    public function getBlockPrefix()
    {
        return '_iodevice_type';
    }
    

}