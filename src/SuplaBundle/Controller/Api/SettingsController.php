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

namespace SuplaBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SettingsController extends RestController {

    /** @var ContainerInterface */
    private $containerWithParameters;

    public function __construct(ContainerInterface $container) {
        $this->containerWithParameters = $container;
    }

    /** @Get("/settings/mqtt-broker") */
    public function getMqttBrokerSettingsAction() {
        $user = $this->getCurrentUserOrThrow();
        $parameters = [
            'enabled' => 'supla.mqtt_broker.enabled',
            'host' => 'supla.mqtt_broker.host',
            'protocol' => 'supla.mqtt_broker.protocol',
            'port' => 'supla.mqtt_broker.port',
            'tls' => 'supla.mqtt_broker.tls',
            'integratedAuth' => 'supla.mqtt_broker.integrated_auth',
        ];
        $parameters = array_map([$this->containerWithParameters, 'getParameter'], $parameters);
        $userSettings = [
            'userEnabled' => $user->isMqttBrokerEnabled(),
            'hasPassword' => $user->hasMqttBrokerAuthPassword(),
            'username' => $user->getShortUniqueId(),
        ];
        return $this->view(array_merge($parameters, $userSettings));
    }

    /**
     * @Post("/settings/mqtt-broker-credentials")
     * @Security("has_role('ROLE_MQTT_BROKER')")
     */
    public function postMqttBrokerCredentialsAction() {
        // TODO 409
        // TODO
        /*
        {
          "host": "beta-mqtt.cloud.supla.org",
          "protocol": "mqtt",
          "port": 8883,
          "tls": true,
          "username": "abcd",
          "password": "$^&*("
        }
         */
    }
}
