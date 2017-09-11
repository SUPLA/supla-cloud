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

namespace SuplaBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class IoDeviceAttributesToStringExtension extends \Twig_Extension {
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function getFilters() {
        return [
            new \Twig_SimpleFilter('channelFunctionToString', [$this, 'channelFunctionToString']),
        ];
    }

    public function channelFunctionToString($function) {
        $ioDeviceManager = $this->container->get('iodevice_manager');
        return $ioDeviceManager->channelFunctionToString($function);
    }

    public function getName() {
        return 'io_device_attributes_to_string';
    }
}
