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
                ->scalarNode('version_full')->defaultValue(null)->end()
                ->scalarNode('autodiscover_url')->defaultValue('https://autodiscover.supla.org')->end()
                ->booleanNode('maintenance_mode')->defaultFalse()->end()
                ->arrayNode('webpack_hashes')->normalizeKeys(false)->defaultValue([])->useAttributeAsKey('name')->prototype('scalar')->end()->end()
                ->arrayNode('clients_registration')->children()
                    ->arrayNode('registration_active_time')->children()
                        ->integerNode('initial')->defaultValue(86400 * 7)->end()
                        ->integerNode('manual')->defaultFalse(86400)->end()
                    ->end()->end()
                ->end()->end()
                ->arrayNode('io_devices_registration')->children()
                    ->arrayNode('registration_active_time')->children()
                        ->integerNode('initial')->defaultValue(86400 * 7)->end()
                        ->integerNode('manual')->defaultFalse(86400)->end()
                    ->end()->end()
                ->end()->end()
                ->arrayNode('brute_force_auth_prevention')->children()
                    ->booleanNode('enabled')->defaultFalse()->end()
                    ->integerNode('max_failed_attempts')->defaultValue(3)->end()
                    ->integerNode('block_time_seconds')->defaultValue(1200)->end() // default 20 minutes
                ->end()->end()
                ->arrayNode('oauth')->children()
                    ->arrayNode('tokens_lifetime')->children()
                        ->arrayNode('webapp')->prototype('integer')->end()->end()
                        ->arrayNode('client_app')->prototype('integer')->end()->end()
                        ->arrayNode('admin')->prototype('integer')->end()->end()
                        ->arrayNode('user')->prototype('integer')->end()->end()
                        ->arrayNode('broker')->prototype('integer')->end()->end()
                    ->end()->end()
                ->end()->end()
                ->arrayNode('maintenance')->children()
                    ->integerNode('delete_non_confirmed_users_older_than_hours')->defaultValue(24)->end()
                    ->integerNode('delete_audit_entries_older_than_days')->defaultValue(60)->end()
                ->end()->end()
            ->end();
        // @formatter:on
        // @codingStandardsIgnoreEnd
        return $treeBuilder;
    }
}
