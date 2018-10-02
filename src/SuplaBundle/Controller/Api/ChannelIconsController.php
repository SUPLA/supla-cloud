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
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Gumlet\ImageResize;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Entity\ChannelIcon;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Repository\ChannelIconRepository;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ChannelIconsController extends RestController {
    use SuplaServerAware;
    use Transactional;

    /** @var ChannelIconRepository */
    private $channelIconRepository;

    public function __construct(ChannelIconRepository $channelIconRepository) {
        $this->channelIconRepository = $channelIconRepository;
    }

    /**
     * @Security("has_role('ROLE_CHANNELS_RW')")
     * @Rest\Post("/channel-icons")
     */
    public function postIconAction(Request $request) {
        $function = $request->get('function');
        Assertion::true(ChannelFunction::isValidKey($function), 'Given function is not valid.');
        /** @var ChannelFunction $function */
        $function = ChannelFunction::$function();
        $icon = new ChannelIcon($this->getUser(), $function);
        $files = $request->files;
        $imagesCount = count($function->getPossibleVisualStates());
        for ($iconIndex = 1; $iconIndex <= $imagesCount; $iconIndex++) {
            Assertion::true($files->has('image' . $iconIndex), "Icon for this function must consist of $imagesCount images.");
            $image = new ImageResize($files->get('image' . $iconIndex)->getPathName());
            $image->resizeToHeight(156, true);
            $image->crop(210, 156);
            $imageString = $image->getImageAsString(IMAGETYPE_PNG);
            $method = 'setImage' . $iconIndex;
            $icon->$method($imageString);
        }
        $this->transactional(function (EntityManagerInterface $em) use ($icon) {
            $em->persist($icon);
        });
        return $this->view($icon);
    }

    /**
     * @Rest\Get("/channel-icons")
     * @Security("has_role('ROLE_CHANNELS_R')")
     */
    public function getChannelIconsAction(Request $request) {
        $criteria = Criteria::create();
        if (($function = $request->get('function')) !== null) {
            $functionIds = array_map(function ($fnc) {
                return ChannelFunction::isValidKey($fnc)
                    ? ChannelFunction::$fnc()->getValue()
                    : (new ChannelFunction((int)$fnc))->getValue();
            }, explode(',', $function));
            $criteria->andWhere(Criteria::expr()->in('function', $functionIds));
        }
        if (($ids = $request->get('ids')) !== null) {
            $criteria->andWhere(Criteria::expr()->in('id', explode(',', $ids)));
        }
        $channels = $this->getUser()->getChannelIcons()->matching($criteria);
        $view = $this->view($channels, Response::HTTP_OK);
        $this->setSerializationGroups($view, $request, ['images']);
        return $view;
    }
}
