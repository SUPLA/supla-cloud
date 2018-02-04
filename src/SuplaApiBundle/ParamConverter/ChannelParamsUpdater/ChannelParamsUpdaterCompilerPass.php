<?php
namespace SuplaApiBundle\ParamConverter\ChannelParamsUpdater;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ChannelParamsUpdaterCompilerPass implements CompilerPassInterface {
    const TAG_NAME = 'supla.channel_params_updater';

    public function process(ContainerBuilder $container) {
        if (!$container->has(ChannelParamsUpdater::class)) {
            return;
        }
        $providerDefinition = $container->findDefinition(ChannelParamsUpdater::class);
        $constraints = $container->findTaggedServiceIds(self::TAG_NAME);
        foreach (array_keys($constraints) as $id) {
            $providerDefinition->addMethodCall('registerChannelUpdater', [new Reference($id)]);
        }
    }
}
