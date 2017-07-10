<?php
namespace SuplaBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class SuplaExtension extends ConfigurableExtension {
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container) {
        $container->setParameter('supla.version', $mergedConfig['version']);
        $container->setParameter('supla.webpack_hashes', $mergedConfig['webpack_hashes']);
    }
}
