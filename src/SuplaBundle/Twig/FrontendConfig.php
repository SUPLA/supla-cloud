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

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FrontendConfig extends AbstractExtension {
    use ContainerAwareTrait;

    const PUBLIC_PARAMETERS = [
        'regulationsAcceptRequired' => 'supla_require_regulations_acceptance',
        'requireCookiePolicyAcceptance' => 'supla_require_cookie_policy_acceptance',
        'recaptchaEnabled' => 'recaptcha_enabled',
        'recaptchaSiteKey' => 'recaptcha_site_key',
        'actAsBrokerCloud' => 'act_as_broker_cloud',
        'suplaUrl' => 'supla_url',
        'maintenanceMode' => 'supla.maintenance_mode',
        'accountsRegistrationEnabled' => 'supla.accounts_registration_enabled',
        'mqttBrokerEnabled' => 'supla.mqtt_broker.enabled',
    ];

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function getFunctions() {
        return [
            new TwigFunction('getFrontendConfig', [$this, 'getConfig']),
        ];
    }

    public function getConfig() {
        $parameters = array_map([$this->container, 'getParameter'], self::PUBLIC_PARAMETERS);
        return array_merge(
            $parameters,
            [
                'max_upload_size' => [
                    'file' => $this->getMaxUploadSizePerFile(),
                    'total' => $this->getMaxUploadSize(),
                ],
            ]
        );
    }

    private function getMaxUploadSizePerFile(): int {
        $perFileLimit = 1024 * (int)ini_get('upload_max_filesize') * (substr(ini_get('upload_max_filesize'), -1) == 'M' ? 1024 : 1);
        return min($perFileLimit, $this->getMaxUploadSize());
    }

    private function getMaxUploadSize(): int {
        return 1024 * (int)ini_get('post_max_size') * (substr(ini_get('post_max_size'), -1) == 'M' ? 1024 : 1);
    }
}
