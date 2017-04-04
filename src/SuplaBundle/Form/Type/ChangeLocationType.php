<?php
/*
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
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangeLocationType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('cancel', ButtonType::class, [
            'label' => 'Cancel',
            'attr' => ['class' => 'overlay-close cancel'],
        ])
            ->add('save', SubmitType::class, ['label' => ' ',
                'attr' => ['class' => 'save pe-7s-check'],
            ]);

        if ($options['action'] != '') {
            $builder->setAction($options['action']);
        }
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'cancel_url' => '',
            'action' => '',
        ]);
    }

    public function getBlockPrefix() {
        return '_change_location_type';
    }
}
