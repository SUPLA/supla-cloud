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

namespace SuplaApiBundle\Controller;

use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use OAuth2\OAuth2;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaApiBundle\Auth\SuplaOAuth2;
use SuplaApiBundle\Entity\OAuth\ApiClient;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Repository\AccessTokenRepository;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiIntegrationsController extends RestController {
    use Transactional;
    use SuplaServerAware;

    /** @var AccessTokenRepository */
    private $accessTokenRepository;
    /** @var SuplaOAuth2 */
    private $oauthServer;
    /** @var ClientManagerInterface */
    private $clientManager;

    public function __construct(
        AccessTokenRepository $accessTokenRepository,
        SuplaOAuth2 $oauthServer,
        ClientManagerInterface $clientManager
    ) {
        $this->accessTokenRepository = $accessTokenRepository;
        $this->oauthServer = $oauthServer;
        $this->clientManager = $clientManager;
    }

    /**
     * @Security("has_role('ROLE_CHANNELGROUPS_R')")
     */
    public function getApplicationsAction(Request $request) {
        $applications = $this->getUser()->getApiClients();
        $view = $this->view($applications, Response::HTTP_OK);
        $this->setSerializationGroups($view, $request, ['secret']);
        return $view;
    }

    /**
     * @Security("client.belongsToUser(user) and has_role('ROLE_CHANNELGROUPS_R')")
     */
    public function getApplicationAction(ApiClient $client, Request $request) {
        $view = $this->view($client, Response::HTTP_OK);
        $this->setSerializationGroups($view, $request, ['secret']);
        return $view;
    }

    /**
     * @Security("has_role('ROLE_CHANNELGROUPS_R')")
     * @Rest\Post("/applications")
     */
    public function postApplicationsAction(ApiClient $newClient, Request $request) {
        $newClient->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE]);
        $newClient->setUser($this->getUser());
        $this->clientManager->updateClient($newClient);
        return $this->getApplicationAction($newClient, $request);
    }

    /**
     * @Security("client.belongsToUser(user) && has_role('ROLE_CHANNELGROUPS_R')")
     */
    public function putApplicationsAction(ApiClient $client, ApiClient $updatedClient, Request $request) {
        $client->setName($updatedClient->getName());
        $client->setDescription($updatedClient->getDescription());
        $client->setRedirectUris($updatedClient->getRedirectUris());
        $this->clientManager->updateClient($client);
        return $this->getApplicationAction($client, $request);
    }

    /**
     * @Security("client.belongsToUser(user) and has_role('ROLE_CHANNELGROUPS_RW')")
     */
    public function deleteApplicationAction(ApiClient $client) {
        return $this->transactional(function (EntityManagerInterface $em) use ($client) {
            $em->remove($client);
            return new Response('', Response::HTTP_NO_CONTENT);
        });
    }

    /**
     * @Rest\Get("/integrations/personal-tokens")
     * @Security("has_role('ROLE_CHANNELGROUPS_R')")
     */
    public function getPersonalTokensAction(Request $request) {
        $accessTokens = $this->accessTokenRepository->findPersonalTokens($this->getUser());
        return $this->view($accessTokens, Response::HTTP_OK);
    }

    /**
     * @Rest\Post("/integrations/personal-tokens")
     * @Security("has_role('ROLE_CHANNELGROUPS_R')")
     */
    public function postPersonalTokensAction(Request $request) {
        $data = $request->request->all();
        Assertion::keyExists($data, 'name');
        Assertion::keyExists($data, 'scopes');
        Assertion::notBlank($data['name'], 'Personal token name is required.');
        Assertion::isArray($data['scopes'], 'Personal token scope is required.');
        $token = $this->transactional(function (EntityManagerInterface $entityManager) use ($data) {
            $token = $this->oauthServer->createPersonalAccessToken($this->getUser(), $data['name'], $data['scopes']);
            $entityManager->persist($token);
            return $token;
        });
        $view = $this->view($token, Response::HTTP_OK);
        $this->setSerializationGroups($view, $request, ['token'], ['token']);
        return $view;
    }

//    /**
//     * @Rest\Get("/channel-groups/{channelGroup}")
//     * @Security("channelGroup.belongsToUser(user) and has_role('ROLE_CHANNELGROUPS_R')")
//     */
//    public function getChannelGroupAction(Request $request, IODeviceChannelGroup $channelGroup) {
//        $view = $this->view($channelGroup, Response::HTTP_OK);
//        $this->setSerializationGroups($view, $request, ['channels', 'iodevice', 'location']);
//        return $view;
//    }
//
//    /**
//     * @Rest\Post("/channel-groups")
//     * @Security("has_role('ROLE_CHANNELGROUPS_RW')")
//     */
//    public function postChannelGroupAction(IODeviceChannelGroup $channelGroup) {
//        $user = $this->getUser();
//        Assertion::lessThan($user->getChannelGroups()->count(), $user->getLimitChannelGroup(), 'Channel group limit has been exceeded');
//        Assertion::lessOrEqualThan(
//            $channelGroup->getChannels()->count(),
//            $user->getLimitChannelPerGroup(),
//            'Too many channels in this group'
//        );
//        return $this->transactional(function (EntityManagerInterface $em) use ($channelGroup) {
//            $em->persist($channelGroup);
//            $this->suplaServer->reconnect();
//            return $this->view($channelGroup, Response::HTTP_CREATED);
//        });
//    }
//
//    /**
//     * @Rest\Put("/channel-groups/{channelGroup}")
//     * @Security("channelGroup.belongsToUser(user) and has_role('ROLE_CHANNELGROUPS_RW')")
//     */
//    public function putChannelGroupAction(IODeviceChannelGroup $channelGroup, IODeviceChannelGroup $updated) {
//        $user = $this->getUser();
//        Assertion::lessOrEqualThan($updated->getChannels()->count(), $user->getLimitChannelPerGroup(), 'Too many channels in this group');
//        return $this->transactional(function (EntityManagerInterface $em) use ($channelGroup, $updated) {
//            $channelGroup->setCaption($updated->getCaption());
//            $channelGroup->setAltIcon($updated->getAltIcon());
//            $channelGroup->setChannels($updated->getChannels());
//            $channelGroup->setHidden($updated->getHidden());
//            $channelGroup->setLocation($updated->getLocation());
//            $em->persist($channelGroup);
//            $this->suplaServer->reconnect();
//            return $this->view($channelGroup, Response::HTTP_CREATED);
//        });
//    }
//
//    /**
//     * @Rest\Delete("/channel-groups/{channelGroup}")
//     * @Security("channelGroup.belongsToUser(user) and has_role('ROLE_CHANNELGROUPS_RW')")
//     */
//    public function deleteChannelGroupAction(IODeviceChannelGroup $channelGroup) {
//        return $this->transactional(function (EntityManagerInterface $em) use ($channelGroup) {
//            $em->remove($channelGroup);
//            $this->suplaServer->reconnect();
//            return new Response('', Response::HTTP_NO_CONTENT);
//        });
//    }
//
//    /**
//     * @Rest\Patch("/channel-groups/{channelGroup}")
//     * @Security("channelGroup.belongsToUser(user) and has_role('ROLE_CHANNELGROUPS_EA')")
//     */
//    public function patchChannelGroupAction(Request $request, IODeviceChannelGroup $channelGroup) {
//        $params = json_decode($request->getContent(), true);
//        Assertion::keyExists($params, 'action', 'Missing action.');
//        $action = ChannelFunctionAction::fromString($params['action']);
//        unset($params['action']);
//        $this->channelActionExecutor->executeAction($channelGroup, $action, $params);
//        return $this->handleView($this->view(null, Response::HTTP_NO_CONTENT));
//    }
}
