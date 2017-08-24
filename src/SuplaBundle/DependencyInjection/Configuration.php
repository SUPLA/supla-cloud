<?php

namespace SuplaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {
    public function getConfigTreeBuilder() {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('supla');
        // @codingStandardsIgnoreStart
        // @formatter:off because indentation makes config structure way clearer
        $rootNode
            ->children()
                ->scalarNode('version')->defaultValue('?.?.?')->end()
                ->arrayNode('webpack_hashes')->normalizeKeys(false)->defaultValue([])->useAttributeAsKey('name')->prototype('scalar')->end()->end()
                ->arrayNode('clients_registration')->children()
                    ->arrayNode('registration_active_time')->children()
                        ->integerNode('initial')->defaultValue(86400 * 7)->end()
                        ->integerNode('manual')->defaultFalse(86400)->end()
                    ->end()
                ->end()
            ->end();
        // @formatter:on
        // @codingStandardsIgnoreEnd
        return $treeBuilder;
    }
}
