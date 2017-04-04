<?php
/*
 src/SuplaBundle/Controller/AuthController.php

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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/auth")
 */
class AuthController extends Controller {
    /**
     * @Route("/login", name="_auth_login")
     */
    public function loginAction(Request $request) {
        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $sl = $this->get('server_list');

        $step = @$request->request->get("step");

        if (@$step != "2"
            && $request->getMethod() == 'POST'
            && strlen(@$request->request->get("_username")) > 3
        ) {
            $step = 2;
            $lastUsername = @$request->request->get("_username");

            $__locale = @$request->request->get("__locale");

            if (in_array($__locale, ['en', 'pl', 'de', 'ru'])) {
                $request->getSession()->set('_locale', $__locale);
                $request->setLocale($__locale);

                $translator = $this->get('translator');
                $translator->setLocale($__locale);
            }
        } else {
            $step = 1;
        }

        return $this->render(
            'SuplaBundle:Auth:login.html.twig',
            [
                'last_username' => $lastUsername,
                'step' => $step,
                'error' => $error,
                'locale' => $request->getLocale(),
                'create_url' => $sl->getCreateAccountUrl($request),
            ]
        );
    }

    /**
     * @Route("/login_check", name="_auth_login_check")
     */
    public function securityCheckAction() {
        // The security layer will intercept this request
    }

    /**
     * @Route("/logout", name="_auth_logout")
     */
    public function logoutAction() {
        // The security layer will intercept this request
    }

    /**
     * @Route("/server", name="_auth_server")
     */
    public function authServer(Request $request) {

        $server = null;
        $data = json_decode($request->getContent());

        $sl = $this->get('server_list');

        $server = $sl->getAuthServerForUser($request, @$data->username);

        return AjaxController::jsonResponse($server !== null, ['server' => $server]);
    }
}
