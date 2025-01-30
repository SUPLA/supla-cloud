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

use Assert\Assertion;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Exception\ApiException;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Repository\ApiClientAuthorizationRepository;
use SuplaBundle\Utils\StringUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use OpenApi\Annotations as OA;

class MqttSettingsController extends RestController {
    use Transactional;

    /** @var ContainerInterface */
    private $containerWithParameters;
    /** @var ApiClientAuthorizationRepository */
    private $apiClientAuthorizationRepository;

    public function __construct(ContainerInterface $container, ApiClientAuthorizationRepository $apiClientAuthorizationRepository) {
        $this->containerWithParameters = $container;
        $this->apiClientAuthorizationRepository = $apiClientAuthorizationRepository;
    }

    /**
     * @OA\Post(
     *     path="/integrations/mqtt-credentials", operationId="createMqttBrokerCredentials",
     *     summary="Creates MQTT Broker credentials for the OAuth Client.", tags={"Integrations"},
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(
     *         @OA\Property(property="host", type="string"),
     *         @OA\Property(property="protocol", type="string"),
     *         @OA\Property(property="port", type="integer"),
     *         @OA\Property(property="tls", type="string"),
     *         @OA\Property(property="username", type="string"),
     *         @OA\Property(property="password", type="string"),
     *     ))
     * )
     * @Post("/integrations/mqtt-credentials")
     * @Security("is_granted('ROLE_MQTT_BROKER')")
     */
    public function postMqttBrokerCredentialsAction() {
        $user = $this->getCurrentUserOrThrow();
        if (!$this->containerWithParameters->getParameter('supla.mqtt_broker.enabled')) {
            throw new ApiException('MQTT Broker is not enabled on this Cloud instance.', Response::HTTP_CONFLICT);
        }
        if (!$user->isMqttBrokerEnabled()) {
            throw new ApiException('User has disabled MQTT support.', Response::HTTP_CONFLICT);
        }
        $client = $this->getCurrentApiClient();
        $authorization = $this->apiClientAuthorizationRepository->findOneByUserAndApiClient($user, $client);
        Assertion::notNull($authorization, 'Application is not authorized by the user.');
        $parameters = [
            'host' => 'supla.mqtt_broker.host',
            'protocol' => 'supla.mqtt_broker.protocol',
            'port' => 'supla.mqtt_broker.port',
            'tls' => 'supla.mqtt_broker.tls',
        ];
        $userSettings = [];
        if ($this->containerWithParameters->getParameter('supla.mqtt_broker.integrated_auth')) {
            [$rawPassword, $encodedPassword] = self::generateMqttBrokerPassword(64);
            $authorization->setMqttBrokerAuthPassword($encodedPassword);
            $this->entityManager->persist($authorization);
            $this->entityManager->flush();
            $userSettings = [
                'username' => $user->getShortUniqueId(),
                'password' => $rawPassword,
            ];
        } else {
            $parameters['username'] = 'supla.mqtt_broker.username';
            $parameters['password'] = 'supla.mqtt_broker.password';
        }
        $parameters = array_map([$this->containerWithParameters, 'getParameter'], $parameters);
        return $this->view(array_merge($parameters, $userSettings), 201);
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
            'hasLocalCredentials' => 'supla.mqtt_broker.password',
        ];
        $parameters = array_map([$this->containerWithParameters, 'getParameter'], $parameters);
        $parameters['hasLocalCredentials'] = !!$parameters['hasLocalCredentials'];
        $userSettings = [
            'userEnabled' => $user->isMqttBrokerEnabled(),
            'hasPassword' => $user->hasMqttBrokerAuthPassword(),
            'username' => $user->getShortUniqueId(),
        ];
        return $this->view(array_merge($parameters, $userSettings));
    }

    public static function generateMqttBrokerPassword(int $lenght = 32): array {
        $rawPassword = StringUtils::randomString($lenght);
        $mqttEncoder = new MessageDigestPasswordEncoder('sha512', false, 1);
        $encodedPassword = $mqttEncoder->encodePassword($rawPassword, null);
        return [$rawPassword, $encodedPassword];
    }
}
