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

use SuplaBundle\EventListener\ApiRateLimit\ApiRateLimitRule;
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
                ->scalarNode('version')->defaultValue('?.?.?')->info('Version is set by the release script.')->end()
                ->scalarNode('version_full')->defaultValue(null)->info('Version with git hash, set by the release script.')->end()
                ->scalarNode('autodiscover_url')->defaultValue('https://autodiscover.supla.org')
                    ->info('The address of the supla-autodiscover service.')->end()
                ->booleanNode('accounts_registration_enabled')->defaultTrue()
                    ->info('If false, the registration of new users will be disabled.')->end()
                ->booleanNode('maintenance_mode')->defaultFalse()
                    ->info('If maintenance mode is true, no modifying actions will be allowed.')->end()
                ->arrayNode('webpack_hashes')->normalizeKeys(false)->defaultValue([])->useAttributeAsKey('script_name')->prototype('scalar')->end()
                    ->info('Information set by the build system. Do not change it.')->end()
                ->arrayNode('clients_registration')->children()
                    ->arrayNode('registration_active_time')->children()
                        ->integerNode('initial')->defaultValue(86400 * 7)
                            ->info('For how many seconds the smartphones registration should be enabled for new accounts.')->end()
                        ->integerNode('manual')->defaultValue(86400)
                            ->info('For how many seconds the smartphones registration should be enabled after clicking "enable".')->end()
                    ->end()->end()
                ->end()->end()
                ->arrayNode('io_devices_registration')->children()
                    ->arrayNode('registration_active_time')->children()
                        ->integerNode('initial')->defaultValue(86400 * 7)
                            ->info('For how many seconds the devices registration should be enabled for new accounts.')->end()
                        ->integerNode('manual')->defaultValue(86400)
                            ->info('For how many seconds the smartphones registration should be enabled after clicking "enable".')->end()
                    ->end()->end()
                ->end()->end()
                ->arrayNode('brute_force_auth_prevention')->children()
                    ->booleanNode('enabled')->defaultFalse()->info('Whether to enable the prevention of guessing the accounts password.')->end()
                    ->integerNode('max_failed_attempts')->defaultValue(3)->info('How many failed attempts to login should result in a ban.')->end()
                    ->integerNode('block_time_seconds')->defaultValue(1200)->info('How long the ban should be (in seconds).')->end() // default 20 minutes
                ->end()->end()
                ->arrayNode('oauth')->children()
                    ->arrayNode('tokens_lifetime')
                        ->info('Lifetime of the OAuth tokens per type, in format [access_token_lifetime, refresh_token_lifetime], in seconds')
                        ->example('[3600, 86400]')->children()
                        ->arrayNode('webapp')->prototype('integer')->end()->end()
                        ->arrayNode('client_app')->prototype('integer')->end()->end()
                        ->arrayNode('admin')->prototype('integer')->end()->end()
                        ->arrayNode('user')->prototype('integer')->end()->end()
                        ->arrayNode('broker')->prototype('integer')->end()->end()
                    ->end()->end()
                ->end()->end()
                ->arrayNode('maintenance')->children()
                    ->integerNode('delete_non_confirmed_users_older_than_hours')->defaultValue(24)
                        ->info('How many hours should be allowed to activate an account.')->end()
                    ->integerNode('delete_audit_entries_older_than_days')->defaultValue(60)
                        ->info('How many days should the audit entries be kept in the database.')->end()
                    ->arrayNode('delete_audit_entries_older_than_days_custom')
                        ->info('Custom rules for keeping the audit entires.')->example('DIRECT_LINK_EXECUTION: 2')
                        ->normalizeKeys(false)->defaultValue([])->useAttributeAsKey('event_name')->prototype('scalar')->end()->end()
                ->end()->end()
            ->end();
            $rootNode->children()
                ->arrayNode('api_rate_limit')->children()
                    ->booleanNode('enabled')->defaultTrue()->info('Should the API requests be counted?')->end()
                    ->booleanNode('blocking')->defaultFalse()
                        ->info('Should the API rate limit excess result in blocking requests? If false, the excesses will be logged only.')->end()
                    ->scalarNode('user_default_limit')
                        ->info('Default limit of API requests for users. In format: requests/seconds')
                        ->example('100/60')->defaultValue('1000/3600')->validate()
                        ->ifTrue(function($v) { return !(new ApiRateLimitRule($v))->isValid(); })->thenInvalid('Rate limit format: requests/seconds')
                    ->end()->end()
                    ->scalarNode('global_limit')
                        ->info('Global limit of API requests. If exceeded, all requests will be considered as exceeding the limit until the reset. In format: requests/seconds')
                        ->example('100/60')->defaultValue('1000/60')->validate()
                        ->ifTrue(function($v) { return !(new ApiRateLimitRule($v))->isValid(); })->thenInvalid('Rate limit format: requests/seconds')
                    ->end()->end()
                ->end()->end()
                ->arrayNode('state_webhooks')->children()
                    ->booleanNode('only_for_public_apps')->defaultFalse()
                        ->info('Set to true, if the state webhooks should be registarable from the public apps only.')->end()
                ->end()->end()
            ->end();
            $rootNode->children()
                ->arrayNode('mqtt_broker')->children()
                    ->booleanNode('enabled')->defaultFalse()->info('Should the MQTT Broker be enabled?')->end()
                    ->scalarNode('host')->info('MQTT Broker address (host)')->end()
                    ->scalarNode('protocol')->info('MQTT Broker protocol')->end()
                    ->scalarNode('port')->info('MQTT Broker port')->end()
                    ->booleanNode('auth')->defaultFalse()->info('Does MQTT Broker require authorization?')->end()
                ->end()->end()
            ->end();
        // @formatter:on
        // @codingStandardsIgnoreEnd
        return $treeBuilder;
    }
}
