<?php 
/*
 src/SuplaBundle/Form/Type/ChannelFunctionType.php

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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\DataTransformer\DataTransformerChain;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToArrayTransformer;
use Symfony\Component\Form\Extension\Core\DataTransformer\ArrayToPartsTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use SuplaBundle\Model\IODeviceManager;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ChannelFunctionType extends AbstractType
{
	
	private $dev_miodevice_manager;
	
	public function __construct(IODeviceManager $iodevice_manager)
	{
		$this->iodevice_manager = $iodevice_manager;
	}
	

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
                'data_class' => 'SuplaBundle\Entity\IODeviceChannel',
            ));
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('function', HiddenType::class);
		$builder->add('param1', HiddenType::class);
		$builder->add('param2', HiddenType::class);
		
	}

	public function buildView(FormView $view, FormInterface $form, array $options)
	{
		$channel = $view->vars['value'];
		
		$view->vars['selected'] = $this->iodevice_manager->channelFunctionToString($channel->getFunction());
		
		$map = $this->iodevice_manager->channelFunctionMap($channel->getType(), $channel->getFuncList());
		$fnc = array();
		
		foreach($map as $f) {
			$fnc[] = array('id' => $f,
					       'name' => $this->iodevice_manager->channelFunctionToString($f)
			);
		}
	
		$view->vars['channel'] = $channel;
		$view->vars['functions'] = $fnc;
		$view->vars['function_params'] = $this->iodevice_manager->channelFunctionParamsHtmlTemplate($channel);
	}

	
	public function getBlockPrefix()
	{
		return 'channelfunction';
	}


}