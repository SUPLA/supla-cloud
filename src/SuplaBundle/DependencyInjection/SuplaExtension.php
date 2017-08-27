<?php

namespace SuplaBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class SuplaExtension extends ConfigurableExtension {
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container) {
        $container->setParameter('supla.version', $mergedConfig['version']);
        $container->setParameter('supla.webpack_hashes', $mergedConfig['webpack_hashes']);
        $container->setParameter(
            'supla.clients_registration.registration_active_time.initial',
            $mergedConfig['clients_registration']['registration_active_time']['initial']
        );
        $container->setParameter(
            'supla.clients_registration.registration_active_time.manual',
            $mergedConfig['clients_registration']['registration_active_time']['manual']
        );
        $container->setParameter(
            'supla.io_devices_registration.registration_active_time.initial',
            $mergedConfig['io_devices_registration']['registration_active_time']['initial']
        );
        $container->setParameter(
            'supla.io_devices_registration.registration_active_time.manual',
            $mergedConfig['io_devices_registration']['registration_active_time']['manual']
        );
    }
}
