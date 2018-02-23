<?php
namespace SuplaApiBundle\Model\ChannelStateGetter;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ChannelStateGetterCompilerPass implements CompilerPassInterface {
    const TAG_NAME = 'supla.channel_state_getter';

    public function process(ContainerBuilder $container) {
        if (!$container->has(ChannelStateGetter::class)) {
            return;
        }
        $providerDefinition = $container->findDefinition(ChannelStateGetter::class);
        $constraints = $container->findTaggedServiceIds(self::TAG_NAME);
        foreach (array_keys($constraints) as $id) {
            $providerDefinition->addMethodCall('registerChannelStateGetter', [new Reference($id)]);
        }
    }
}
