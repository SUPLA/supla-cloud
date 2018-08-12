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

use Assert\Assertion;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SuplaBundle\Entity\DirectLink;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ExecuteDirectLinkController extends Controller {
    /** @var ChannelActionExecutor */
    private $channelActionExecutor;

    public function __construct(ChannelActionExecutor $channelActionExecutor) {
        $this->channelActionExecutor = $channelActionExecutor;
    }

    /**
     * @Route("/direct/{directLink}/{slug}/{action}")
     */
    public function executeDirectLinkAction(DirectLink $directLink, string $slug, string $action) {
        $action = ChannelFunctionAction::fromString($action);
        Assertion::inArray($action->getId(), EntityUtils::mapToIds($directLink->getAllowedActions()));
        // TODO check slug
        $this->channelActionExecutor->executeAction($directLink->getChannel(), $action);
        return new Response('', Response::HTTP_ACCEPTED);
    }
}
