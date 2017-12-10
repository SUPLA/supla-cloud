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
use SuplaBundle\Entity\AccessID;
use SuplaBundle\Entity\User;
use SuplaBundle\Form\Type\AssignType;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/aid")
 */
class AccessIDController extends AbstractController {
    use SuplaServerAware;

    private function userReconnect() {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $this->suplaServer->reconnect($user->getId());
    }

    private function getAccessIdById($id) {
        $aid_man = $this->get('accessid_manager');
        return $aid_man->accessIdById($id);
    }

    private function getAccessIdDetails($id) {
        $aid = $this->getAccessIdById($id);
        if ($aid !== null) {
            return $this->get('templating')->render(
                'SuplaBundle:AccessID:aiddetails.html.twig',
                ['accessid' => $aid,
                    'locations' => $aid->getLocations(),
                ]
            );
        };

        return null;
    }

    /**
     * @Route("", name="_aid_list")
     */
    public function listAction() {
        /** @var User $user */
        $user = $this->getUser();

        if ($this->expectsJsonResponse()) {
            return $this->jsonResponse($user->getAccessIDS());
        } else {
            $details = '';

            $id = intval($this->get('session')->get('_aid_details_lastid'));

            if ($this->getAccessIdById($id) === null && $user->getAccessIDS()->count() > 0) {
                $id = $user->getAccessIDS()->get($user->getAccessIDS()->count() - 1)->getId();
            }

            if ($id !== null && $id !== 0) {
                $details = $this->getAccessIdDetails($id);

                if ($details === null) {
                    $details = '';
                }
            }

            return $this->render(
                'SuplaBundle:AccessID:aidlist.html.twig',
                ['accessids' => $user->getAccessIds(),
                    'aid_selected' => $id === null ? 0 : $id,
                    'details' => $details,
                ]
            );
        }
    }

    /**
     * @Route("/new", name="_aid_new")
     */
    public function newAction() {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $aid_man = $this->get('accessid_manager');

        if ($user->getLimitLoc() > 0
            && $aid_man->totalCount($user) >= $user->getLimitAid()
        ) {
            $this->get('session')->getFlashBag()->add('error', [
                'title' => 'Stop',
                'message' => 'Access identifier limit has been exceeded',
            ]);
        } else {
            $aid = $aid_man->createID($user);

            if ($aid !== null) {
                $m = $this->get('doctrine')->getManager();
                $m->persist($aid);
                $m->flush();
                $this->get('session')->getFlashBag()->add('success', [
                    'title' => 'Success',
                    'message' => 'New access identifier has been created',
                ]);
                $this->get('session')->set('_aid_details_lastid', $aid->getId());
            } else {
                $this->get('session')->getFlashBag()->add('error', ['title' => 'Error', 'message' => 'Unknown error']);
            }
        }

        return $this->redirectToRoute("_aid_list");
    }

    /**
     * @Route("/{id}/view", name="_aid_item")
     */
    public function itemViewAction(Request $request, $id) {
        $aid = $this->getAccessIdById($id);

        if ($aid !== null) {
            $this->get('session')->set('_aid_details_lastid', $aid->getId());
        }

        return $this->redirectToRoute("_aid_list");
    }

    /**
     * @Route("/{id}/delete", name="_aid_item_delete")
     */
    public function itemDeleteAction($id) {
        $aid = $this->getAccessIdById($id);

        if ($aid === null) {
            return $this->redirectToRoute("_aid_list");
        }

        $m = $this->get('doctrine')->getManager();
        $m->remove($aid);
        $m->flush();

        $this->userReconnect();

        $this->get('session')->getFlashBag()->add('warning', ['title' => 'Information', 'message' => 'Access identifier has been deleted']);
        return $this->redirectToRoute("_aid_list");
    }

    /**
     * @Route("/{id}/assignloc", name="_loc_assignloc")
     */
    public function assignLocAction(Request $request, $id) {
        $aid = $this->getAccessIdById($id);

        if ($aid === null) {
            return $this->redirectToRoute("_aid_list");
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $form = $this->createForm(
            AssignType::class,
            null,
            ['cancel_url' => $this->generateUrl('_aid_list')]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sel_lid = $request->request->get('lid');
            $locs = $aid->getLocations();

            $aid_man = $this->get('accessid_manager');
            $loc_man = $this->get('location_manager');

            // remove
            foreach ($locs->getKeys() as $key) {
                $loc = $locs->get($key);

                if ($loc !== null
                    && (is_array($sel_lid) === false
                        || array_key_exists($loc->getId(), $sel_lid) == false
                        || $sel_lid[$loc->getId()] != '1')
                ) {
                    $loc->getAccessIds()->removeElement($aid);
                }
            }

            // add new
            if (is_array($sel_lid) === true) {
                foreach ($sel_lid as $key => $value) {
                    if ($value == '1') {
                        $loc = $loc_man->locationById(intval($key));
                        if ($loc !== null
                            && $locs->contains($loc) === false
                        ) {
                            $loc->getAccessIds()->add($aid);
                        }
                    }
                }
            }

            $m = $this->get('doctrine')->getManager();
            $m->flush();

            $this->userReconnect();

            $this->get('session')->getFlashBag()->add('success', ['title' => 'Success', 'message' => 'Data saved!']);
        }

        return $this->redirectToRoute("_aid_list");
    }

    /**
     * @Route("/{id}/ajax/assign_list", name="_aid_ajax_assign_list")
     */
    public function ajaxAssignLocList(Request $request, $id) {
        $html = null;
        $aid = $this->getAccessIdById($id);

        if ($aid !== null) {
            $user = $this->get('security.token_storage')->getToken()->getUser();

            $form = $this->createForm(
                AssignType::class,
                null,
                ['cancel_url' => $this->generateUrl('_aid_list'),
                    'action' => $this->generateUrl('_loc_assignloc', ['id' => $aid->getId()]),
                ]
            );

            $html = $this->get('templating')->render(
                'SuplaBundle:AccessID:assignloc.html.twig',
                ['form' => $form->createView(),
                    'accessid' => $aid,
                    'locations' => $user->getLocations(),
                    'selected' => $aid->getLocations(),
                ]
            );
        }

        return AjaxController::jsonResponse($html !== null, ['html' => $html]);
    }

    /**
     * @Route("/{id}/ajax/getdetails", name="_aid_ajax_getdetails")
     */
    public function ajaxGetDetails(Request $request, $id) {
        $result = false;
        $html = null;

        $html = $this->getAccessIdDetails($id);

        if ($html !== null) {
            $this->get('session')->set('_aid_details_lastid', intval($id));
        }

        return AjaxController::jsonResponse($html !== null, ['html' => $html]);
    }

    private function ajaxItemEdit(AccessID $aid, $message, $value) {
        $result = AjaxController
            ::itemEdit($this->get('validator'), $this->get('translator'), $this->get('doctrine'), $aid, $message, $value);
        $this->userReconnect();
        return $result;
    }

    /**
     * @Route("/{id}/ajax/setenabled/{enabled}", name="_aid_ajax_setenabled")
     */
    public function ajaxSetEnabled(Request $request, $id, $enabled) {
        $aid = $this->getAccessIdById($id);

        if ($aid !== null) {
            $aid->setEnabled($enabled == '1');
        }

        return $this->ajaxItemEdit(
            $aid,
            'Access identifier has been ' . ($enabled == '1' ? 'enabled' : 'disabled'),
            $this->get('translator')->trans($enabled == '1' ? 'Enabled' : 'Disabled')
        );
    }

    private function ajaxItemSet(Request $request, $id, $caption, $field) {

        $aid = $this->getAccessIdById($id);

        if ($aid !== null) {
            $data = json_decode($request->getContent());

            if ($caption === true) {
                $aid->setCaption($data->value);
            } else {
                $aid->setPassword($data->value);
            }
        }

        return $this->ajaxItemEdit(
            $aid,
            $field . ' has been changed',
            null
        );
    }

    /**
     * @Route("/{id}/ajax/setcaption", name="_aid_ajax_setcaption")
     */
    public function ajaxSetCaption(Request $request, $id) {
        return $this->ajaxItemSet($request, $id, true, 'Caption');
    }

    /**
     * @Route("/{id}/ajax/setpwd", name="_aid_ajax_setpwd")
     */
    public function ajaxSetPwd(Request $request, $id) {
        return $this->ajaxItemSet($request, $id, false, 'Password');
    }
}
