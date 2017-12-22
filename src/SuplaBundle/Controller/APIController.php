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
use SuplaApiBundle\Form\Model\ChangeApiPassword;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class APIController extends Controller {

    /**
     * @Route("/api-settings", name="_api_settings")
     */
    public function apiSettingsAction() {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $api_man = $this->get('api_manager');
        $client = $api_man->getClient($user);
        $api_user = $api_man->getAPIUser($user);

        $url = $this->generateUrl('fos_oauth_server_token', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->render(
            'SuplaBundle:API:settings.html.twig',
            ['user' => $user,
                'client' => $client,
                'api_user' => $api_user,
                'token_url' => $url,
                'server' => $this->container->getParameter('supla_protocol') . '://' . $this->container->getParameter('supla_server'),
            ]
        );
    }

    /**
     * @Route("/api-changepassword", name="_api_ajax_changepassword")
     */
    public function ajaxChangePassword(Request $request) {
        $data = json_decode($request->getContent());
        $translator = $this->get('translator');
        $validator = $this->get('validator');

        $cp = new ChangeApiPassword();
        $cp->setNewPassword(@$data->new_password);

        $errors = $validator->validate($cp);

        if (count($errors) > 0) {
            $result = ['flash' => ['title' => $translator->trans('Error'),
                'message' => $translator->trans($errors[0]->getMessage()),
                'type' => 'error'],
            ];

            return AjaxController::jsonResponse(false, $result);
        };

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $api_man = $this->get('api_manager');
        $api_user = $api_man->getAPIUser($user);

        $api_man->setPassword($data->new_password, $api_user, false);

        return AjaxController::itemEdit($validator, $translator, $this->get('doctrine'), $api_user, 'Password has been changed!', '');
    }

    /**
     * @Route("/api-settings/ajax/setenabled/{enabled}", name="_api_ajax_setenabled")
     */
    public function ajaxSetEnabled(Request $request, $enabled) {
        $enabled = $enabled == '1';
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $api_man = $this->get('api_manager');
        $translator = $this->get('translator');

        $api_user = $api_man->getAPIUser($user);

        $api_man->setEnabled($enabled, $api_user, true);

        $result = ['flash' => ['title' => $translator->trans('Success'),
            'message' => $translator->trans('RESTful API has been ' . ($enabled ? 'enabled' : 'disabled')),
            'type' => 'success'],
            'value' => $this->get('translator')->trans($enabled == '1' ? 'Enabled' : 'Disabled'),
        ];

        return AjaxController::jsonResponse(true, $result);
    }
}
