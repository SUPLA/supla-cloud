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
use SuplaBundle\Entity\Scene;
use SuplaBundle\Entity\SceneOperation;
use SuplaBundle\Model\Transactional;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ScenesController extends RestController {
    use Transactional;

    /**
     * @Rest\Get("/scenes")
     * @Security("has_role('ROLE_SCENES_R')")
     */
    public function getScenesAction(Request $request) {
        $scenes = $this->getUser()->getScenes();
        $view = $this->view($scenes, Response::HTTP_OK);
        $this->setSerializationGroups($view, $request, ['subject', 'operations']);
        return $view;
    }

    /**
     * @Rest\Get("/scenes/{scene}")
     * @Security("scene.belongsToUser(user) and has_role('ROLE_SCENES_R')")
     */
    public function getSceneAction(Request $request, Scene $scene) {
        $view = $this->view($scene, Response::HTTP_OK);
        $this->setSerializationGroups($view, $request, ['subject', 'operations']);
        return $view;
    }

    /**
     * @Rest\Post("/scenes")
     * @Security("has_role('ROLE_SCENES_RW')")
     */
    public function postSceneAction(Request $request, Scene $scene) {
        $user = $this->getUser();
        if (!$scene->getCaption()) {
            $caption = $this->get('translator')
                ->trans('Scene', [], null, $user->getLocale()); // i18n
            $scene->setCaption($caption . ' #' . ($user->getScenes()->count() + 1));
        }
        Assertion::false($user->isLimitSceneExceeded(), 'Scenes limit has been exceeded'); // i18n
        $scene = $this->transactional(function (EntityManagerInterface $em) use ($scene) {
            $scene->setEnabled(true);
            $em->persist($scene);
            return $scene;
        });
        $view = $this->view($scene, Response::HTTP_CREATED);
        $this->setSerializationGroups($view, $request, ['subject', 'operations']);
        return $view;
    }

    /**
     * @Rest\Put("/scenes/{scene}")
     * @Security("scene.belongsToUser(user) and has_role('ROLE_SCENES_RW')")
     */
    public function putSceneAction(Scene $scene, Scene $updated, Request $request) {
        return $this->transactional(function (EntityManagerInterface $em) use ($request, $scene, $updated) {
            $scene->setCaption($updated->getCaption());
            $scene->setEnabled($updated->isEnabled());
            $scene->getOperations()->forAll(function (int $index, SceneOperation $sceneOperation) use ($em) {
                $em->remove($sceneOperation);
            });
            $scene->setOpeartions($updated->getOperations());
            $em->persist($scene);
            return $this->getSceneAction($request, $scene);
        });
    }

    /**
     * @Rest\Delete("/scenes/{scene}")
     * @Security("scene.belongsToUser(user) and has_role('ROLE_SCENES_RW')")
     */
    public function deleteSceneAction(Scene $scene) {
        return $this->transactional(function (EntityManagerInterface $em) use ($scene) {
            $em->remove($scene);
            return new Response('', Response::HTTP_NO_CONTENT);
        });
    }
}
