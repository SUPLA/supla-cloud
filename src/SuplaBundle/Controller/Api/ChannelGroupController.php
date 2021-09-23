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
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Auth\Voter\AccessIdSecurityVoter;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\IODeviceChannelGroup;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\EventListener\UnavailableInMaintenance;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Model\Dependencies\ChannelGroupDependencies;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Repository\ChannelGroupRepository;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ChannelGroupController extends RestController {
    use Transactional;
    use SuplaServerAware;

    /** @var ChannelActionExecutor */
    private $channelActionExecutor;
    /** @var ChannelGroupRepository */
    private $channelGroupRepository;

    public function __construct(ChannelActionExecutor $channelActionExecutor, ChannelGroupRepository $channelGroupRepository) {
        $this->channelActionExecutor = $channelActionExecutor;
        $this->channelGroupRepository = $channelGroupRepository;
    }

    protected function getDefaultAllowedSerializationGroups(Request $request): array {
        $groups = [
            'iodevice', 'location', 'state', 'relationsCount',
            'location' => 'channelGroup.location',
            'iodevice' => 'channel.iodevice',
            'relationsCount' => 'channelGroup.relationsCount',
        ];
        if (!strpos($request->get('_route'), 'channelGroups_list')) {
            $groups[] = 'channels';
            $groups['channels'] = 'channelGroup.channels';
        }
        return $groups;
    }

    /** @return Collection|IODeviceChannelGroup[] */
    private function returnChannelGroups(): Collection {
        return $this->channelGroupRepository->findAllForUser($this->getUser())
            ->filter(function (IODeviceChannelGroup $channelGroup) {
                return $this->isGranted(AccessIdSecurityVoter::PERMISSION_NAME, $channelGroup);
            });
    }

    /**
     * @Rest\Get(path="/channel-groups", name="channelGroups_list")
     * @Security("has_role('ROLE_CHANNELGROUPS_R')")
     */
    public function getChannelGroupsAction(Request $request) {
        return $this->serializedView($this->returnChannelGroups()->getValues(), $request);
    }

    /**
     * @Security("channel.belongsToUser(user) and has_role('ROLE_CHANNELS_R')")
     * @Rest\Get("/channels/{channel}/channel-groups", name="channels_channelGroups_list")
     */
    public function getChannelChannelGroupsAction(IODeviceChannel $channel, Request $request) {
        $channelGroups = $this->returnChannelGroups()
            ->filter(function (IODeviceChannelGroup $channelGroup) use ($channel) {
                return in_array($channel->getId(), EntityUtils::mapToIds($channelGroup->getChannels()));
            });
        return $this->serializedView($channelGroups->getValues(), $request);
    }

    /**
     * @Rest\Get("/channel-groups/{channelGroup}")
     * @Security("channelGroup.belongsToUser(user) and has_role('ROLE_CHANNELGROUPS_R') and is_granted('accessIdContains', channelGroup)")
     */
    public function getChannelGroupAction(Request $request, IODeviceChannelGroup $channelGroup) {
        return $this->serializedView($channelGroup, $request, ['location.relationsCount', 'channelGroup.relationsCount']);
    }

    /**
     * @Rest\Post("/channel-groups")
     * @Security("has_role('ROLE_CHANNELGROUPS_RW')")
     * @UnavailableInMaintenance
     */
    public function postChannelGroupAction(IODeviceChannelGroup $channelGroup, Request $request) {
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
        Assertion::notInArray(
            $channelGroup->getFunction()->getId(),
            [ChannelFunction::DIGIGLASS_VERTICAL, ChannelFunction::DIGIGLASS_HORIZONTAL],
            'Channels groups are not supported for this function.' // i18n
        );
        $channelGroup = $this->transactional(function (EntityManagerInterface $em) use ($channelGroup) {
            $em->persist($channelGroup);
            return $channelGroup;
        });
        $this->suplaServer->reconnect();
        return $this->serializedView($channelGroup, $request, ['channelGroup.relationsCount'], Response::HTTP_CREATED);
    }

    /**
     * @Rest\Put("/channel-groups/{channelGroup}")
     * @Security("channelGroup.belongsToUser(user) and has_role('ROLE_CHANNELGROUPS_RW') and is_granted('accessIdContains', channelGroup)")
     * @UnavailableInMaintenance
     */
    public function putChannelGroupAction(IODeviceChannelGroup $channelGroup, IODeviceChannelGroup $updated, Request $request) {
        $user = $this->getUser();
        Assertion::lessOrEqualThan(
            $updated->getChannels()->count(),
            $user->getLimitChannelPerGroup(),
            'Too many channels in this group' // i18n
        );
        $channelGroup = $this->transactional(function (EntityManagerInterface $em) use ($channelGroup, $updated) {
            $channelGroup->setCaption($updated->getCaption());
            $channelGroup->setAltIcon($updated->getAltIcon());
            $channelGroup->setUserIcon($updated->getUserIcon());
            $channelGroup->setChannels($updated->getChannels());
            $channelGroup->setHidden($updated->getHidden());
            $channelGroup->setLocation($updated->getLocation());
            $em->persist($channelGroup);
            return $channelGroup;
        });
        $this->suplaServer->reconnect();
        return $this->getChannelGroupAction($request, $channelGroup->clearRelationsCount());
    }

    /**
     * @Rest\Delete("/channel-groups/{channelGroup}")
     * @Security("channelGroup.belongsToUser(user) and has_role('ROLE_CHANNELGROUPS_RW') and is_granted('accessIdContains', channelGroup)")
     * @UnavailableInMaintenance
     */
    public function deleteChannelGroupAction(
        Request $request,
        IODeviceChannelGroup $channelGroup,
        ChannelGroupDependencies $channelGroupDependencies
    ) {
        $result = $this->transactional(function (EntityManagerInterface $em) use ($channelGroupDependencies, $request, $channelGroup) {
            if ($request->get('safe', false)) {
                $dependencies = $channelGroupDependencies->getDependencies($channelGroup);
                if (array_filter($dependencies)) {
                    return $this->view($dependencies, Response::HTTP_CONFLICT);
                }
            } else {
                $channelGroupDependencies->clearDependencies($channelGroup);
            }
            $em->remove($channelGroup);
            return new Response('', Response::HTTP_NO_CONTENT);
        });
        if ($result->getStatusCode() === Response::HTTP_NO_CONTENT) {
            $this->suplaServer->reconnect();
        }
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
