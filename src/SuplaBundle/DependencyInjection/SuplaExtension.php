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

use AppKernel;
use SuplaBundle\Enums\ApiClientType;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class SuplaExtension extends ConfigurableExtension {
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container) {
        $container->setParameter('supla.version', $mergedConfig['version']);
        $container->setParameter('supla.version_full', $mergedConfig['version_full']);
        $container->setParameter('supla.maintenance_mode', $mergedConfig['maintenance_mode']);
        $container->setParameter('supla.autodiscover_url', $mergedConfig['autodiscover_url']);
        $container->setParameter('supla.accounts_registration_enabled', $mergedConfig['accounts_registration_enabled']);
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
        $container->setParameter('supla.brute_force_auth_prevention.enabled', $mergedConfig['brute_force_auth_prevention']['enabled']);
        $container->setParameter(
            'supla.brute_force_auth_prevention.max_failed_attempts',
            $mergedConfig['brute_force_auth_prevention']['max_failed_attempts']
        );
        $container->setParameter(
            'supla.brute_force_auth_prevention.block_time_seconds',
            $mergedConfig['brute_force_auth_prevention']['block_time_seconds']
        );
        $container->setParameter(
            'supla.maintenance.delete_non_confirmed_users_older_than_hours',
            $mergedConfig['maintenance']['delete_non_confirmed_users_older_than_hours']
        );
        $container->setParameter(
            'supla.maintenance.delete_audit_entries_older_than_days',
            $mergedConfig['maintenance']['delete_audit_entries_older_than_days']
        );
        $container->setParameter(
            'supla.maintenance.delete_audit_entries_older_than_days_custom',
            $mergedConfig['maintenance']['delete_audit_entries_older_than_days_custom']
        );
        $container->setParameter('supla.oauth.tokens_lifetime', $this->buildOauthTokensConfig($mergedConfig['oauth']['tokens_lifetime']));
        $container->setParameter('supla.available_languages', $this->detectAvailableLanguages());
        $container->setParameter('supla.api_rate_limit.enabled', $mergedConfig['api_rate_limit']['enabled']);
        $container->setParameter('supla.api_rate_limit.blocking', $mergedConfig['api_rate_limit']['blocking']);
        $container->setParameter('supla.api_rate_limit.global_limit', $mergedConfig['api_rate_limit']['global_limit']);
        $container->setParameter('supla.api_rate_limit.user_default_limit', $mergedConfig['api_rate_limit']['user_default_limit']);
        $container->setParameter(
            'supla.state_webhooks.only_for_public_apps',
            ($mergedConfig['state_webhooks'] ?? [])['only_for_public_apps'] ?? false
        );
        $container->setParameter('supla.mqtt_broker.enabled', $mergedConfig['mqtt_broker']['enabled']);
        $container->setParameter('supla.mqtt_broker.host', $mergedConfig['mqtt_broker']['host']);
        $container->setParameter('supla.mqtt_broker.protocol', $mergedConfig['mqtt_broker']['protocol']);
        $container->setParameter('supla.mqtt_broker.port', $mergedConfig['mqtt_broker']['port']);
        $container->setParameter('supla.mqtt_broker.tls', $mergedConfig['mqtt_broker']['tls']);
        $container->setParameter('supla.mqtt_broker.integrated_auth', $mergedConfig['mqtt_broker']['integrated_auth']);
        $container->setParameter('supla.mqtt_broker.username', $mergedConfig['mqtt_broker']['username']);
        $container->setParameter('supla.mqtt_broker.password', $mergedConfig['mqtt_broker']['password']);
        $container->setParameter('supla.measurement_logs_retention', $mergedConfig['measurement_logs_retention']);
        $container->setParameter('supla.ocr.enabled', $mergedConfig['ocr']['enabled']);
        $container->setParameter('supla.ocr.url', $mergedConfig['ocr']['url']);
    }

    private function buildOauthTokensConfig(array $tokensLifetimes): array {
        $mapped = [];
        foreach ($tokensLifetimes as $clientType => $lifetimes) {
            $clientType = strtoupper($clientType);
            $id = ApiClientType::$clientType()->getValue();
            $mapped[$id] = [
                'access' => $lifetimes['access'],
                'refresh' => $lifetimes['refresh'] ?? 5184000,
            ];
        }
        return $mapped;
    }

    private function detectAvailableLanguages() {
        $files = scandir(AppKernel::ROOT_PATH . '/../src/SuplaBundle/Resources/translations');
        $languages = array_map(function ($path) {
            preg_match('#\.([a-z]{2})\.yml$#', $path, $match);
            return $match ? $match[1] ?? null : null;
        }, $files);
        return array_values(array_filter($languages));
    }
}
