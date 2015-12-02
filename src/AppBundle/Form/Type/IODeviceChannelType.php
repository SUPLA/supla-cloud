<?php 
/*
 src/AppBundle/Form/Type/IODeviceType.php

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
use AppBundle\Form\Type\ChannelFunctionType;

/**
 * @author Przemyslaw Zygmunt p.zygmunt@acsoftware.pl [AC SOFTWARE]
 */
class IODeviceChannelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('channel', 'channelfunction', array('label' => 'Function'))        
    	->add('caption', 'text', array('label' => 'Caption', 'required' => false))
    	->add('cancel', 'button', array(
    			'label' => ' ',
    			'attr' => array('class' => 'back pe-7s-left-arrow', 'onClick' => "fadeToUrl('".$options['cancel_url']."')"),
    	))
    	->add('save', 'submit', array('label' => ' ',
    			'attr' => array('class' => 'save pe-7s-check')
    	));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\IODeviceChannel',
        	'cancel_url' => ''
        ));
    }

    public function getName()
    {
        return '_iodevice_channel_type';
    }
    

}