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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/auth")
 */
class AuthController extends AbstractController {
    /**
     * @Route("/login", name="_auth_login")
     * @Template
     */
    public function loginAction() {
        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        return [
            'last_username' => $lastUsername,
            'error' => !!$error,
        ];
    }

    /**
     * @Route("/create")
     */
    public function createAccountRedirectAction(Request $request) {
        $sl = $this->get('server_list');
        return $this->redirect($sl->getCreateAccountUrl($request));
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
     * @Route("/servers", name="_auth_server")
     */
    public function authServer(Request $request) {
        $username = $request->get('username', '');
        $serverList = $this->get('server_list');
        $server = $serverList->getAuthServerForUser($request, $username);
        return $this->jsonResponse(['server' => $server]);
    }
}
