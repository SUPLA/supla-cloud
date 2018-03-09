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

namespace SuplaBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/channel-groups")
 */
class ChannelGroupController extends AbstractController {
    /**
     * @Route("", methods={"GET"}, name="_channel_groups_list")
     * @Template
     */
    public function channelGroupsListAction() {
        return ['id' => ''];
    }

    /**
     * @Route("/{id}", methods={"GET"}, requirements={"id" = "^\d{1,10}$"}, name="_channel_groups_details")
     * @Template("SuplaBundle:ChannelGroup:channelGroupsList.html.twig");
     */
    public function channelGroupDetailsAction(int $id) {
        return ['id' => $id];
    }
}
