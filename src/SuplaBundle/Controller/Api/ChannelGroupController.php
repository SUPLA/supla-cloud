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
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Auth\Voter\AccessIdSecurityVoter;
use SuplaBundle\Entity\IODeviceChannelGroup;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ChannelGroupController extends RestController {
    use Transactional;
    use SuplaServerAware;

    /** @var ChannelActionExecutor */
    private $channelActionExecutor;

    public function __construct(ChannelActionExecutor $channelActionExecutor) {
        $this->channelActionExecutor = $channelActionExecutor;
    }

    /**
     * @Rest\Get("/channel-groups")
     * @Security("has_role('ROLE_CHANNELGROUPS_R')")
     */
    public function getChannelGroupsAction(Request $request) {
        $channelGroups = $this->getUser()->getChannelGroups();
        $channelGroups = $channelGroups->filter(function (IODeviceChannelGroup $channelGroup) {
            return $this->isGranted(AccessIdSecurityVoter::PERMISSION_NAME, $channelGroup);
        });
        $view = $this->view($channelGroups->getValues(), Response::HTTP_OK);
        $this->setSerializationGroups($view, $request, ['channels']);
        return $view;
    }

    /**
     * @Rest\Get("/channel-groups/{channelGroup}")
     * @Security("channelGroup.belongsToUser(user) and has_role('ROLE_CHANNELGROUPS_R') and is_granted('accessIdContains', channelGroup)")
     */
    public function getChannelGroupAction(Request $request, IODeviceChannelGroup $channelGroup) {
        $view = $this->view($channelGroup, Response::HTTP_OK);
        $this->setSerializationGroups($view, $request, ['channels', 'iodevice', 'location', 'state', 'relationsCount']);
        return $view;
    }

    /**
     * @Rest\Post("/channel-groups")
     * @Security("has_role('ROLE_CHANNELGROUPS_RW')")
     */
    public function postChannelGroupAction(IODeviceChannelGroup $channelGroup) {
        $user = $this->getUser();
        Assertion::lessThan(
            $user->getChannelGroups()->count(),
            $user->getLimitChannelGroup(),
            'Channel group limit has been exceeded' // i18n
        );
        Assertion::lessOrEqualThan(
            $channelGroup->getChannels()->count(),
            $user->getLimitChannelPerGroup(),
            'Too many channels in this group' // i18n
        );
        $result = $this->transactional(function (EntityManagerInterface $em) use ($channelGroup) {
            $em->persist($channelGroup);
            return $this->view($channelGroup, Response::HTTP_CREATED);
        });
        $this->suplaServer->reconnect();
        return $result;
    }

    /**
     * @Rest\Put("/channel-groups/{channelGroup}")
     * @Security("channelGroup.belongsToUser(user) and has_role('ROLE_CHANNELGROUPS_RW') and is_granted('accessIdContains', channelGroup)")
     */
    public function putChannelGroupAction(IODeviceChannelGroup $channelGroup, IODeviceChannelGroup $updated) {
        $user = $this->getUser();
        Assertion::lessOrEqualThan(
            $updated->getChannels()->count(),
            $user->getLimitChannelPerGroup(),
            'Too many channels in this group' // i18n
        );
        $result = $this->transactional(function (EntityManagerInterface $em) use ($channelGroup, $updated) {
            $channelGroup->setCaption($updated->getCaption());
            $channelGroup->setAltIcon($updated->getAltIcon());
            $channelGroup->setUserIcon($updated->getUserIcon());
            $channelGroup->setChannels($updated->getChannels());
            $channelGroup->setHidden($updated->getHidden());
            $channelGroup->setLocation($updated->getLocation());
            $em->persist($channelGroup);
            return $this->view($channelGroup, Response::HTTP_OK);
        });
        $this->suplaServer->reconnect();
        return $result;
    }

    /**
     * @Rest\Delete("/channel-groups/{channelGroup}")
     * @Security("channelGroup.belongsToUser(user) and has_role('ROLE_CHANNELGROUPS_RW') and is_granted('accessIdContains', channelGroup)")
     */
    public function deleteChannelGroupAction(IODeviceChannelGroup $channelGroup) {
        $result = $this->transactional(function (EntityManagerInterface $em) use ($channelGroup) {
            $em->remove($channelGroup);
            return new Response('', Response::HTTP_NO_CONTENT);
        });
        $this->suplaServer->reconnect();
        return $result;
    }

    /**
     * @Rest\Patch("/channel-groups/{channelGroup}")
     * @Security("channelGroup.belongsToUser(user) and has_role('ROLE_CHANNELGROUPS_EA') and is_granted('accessIdContains', channelGroup)")
     */
    public function patchChannelGroupAction(Request $request, IODeviceChannelGroup $channelGroup) {
        $params = json_decode($request->getContent(), true);
        Assertion::keyExists($params, 'action', 'Missing action.');
        $action = ChannelFunctionAction::fromString($params['action']);
        unset($params['action']);
        $this->channelActionExecutor->executeAction($channelGroup, $action, $params);
        $status = ApiVersions::V2_3()->isRequestedEqualOrGreaterThan($request) ? Response::HTTP_ACCEPTED : Response::HTTP_NO_CONTENT;
        return $this->handleView($this->view(null, $status));
    }
}
