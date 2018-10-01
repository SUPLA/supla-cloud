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

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Gumlet\ImageResize;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Entity\ChannelIcon;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ChannelIconsController extends RestController {
    use SuplaServerAware;
    use Transactional;

    /**
     * @Security("has_role('ROLE_CHANNELS_RW')")
     * @Rest\Post("/channel-icons")
     */
    public function postIconsAction(Request $request) {
        $files = $request->files;
        $image = new ImageResize($files->get('opened')->getPathName());
//        $image->resizeToBestFit(216, 156);
        $image->resizeToHeight(156, true);
        $image->crop(210, 156);
        $imageString = $image->getImageAsString(IMAGETYPE_PNG);
        $icon = new ChannelIcon($this->getUser(), ChannelFunction::CONTROLLINGTHEGATE());
        $icon->setImage1($imageString);
        $this->transactional(function (EntityManagerInterface $em) use ($icon) {
            $em->persist($icon);
        });
        return new Response(base64_encode($imageString));
    }
}
