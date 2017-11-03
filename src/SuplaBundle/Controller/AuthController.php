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
use Symfony\Component\HttpFoundation\RedirectResponse;
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
        if ($this->getUser()) {
            return $this->redirectToRoute('_homepage');
        }
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
        if ($this->getUser()) {
            return $this->redirectToRoute('_homepage');
        }
        $sl = $this->get('server_list');
        return $this->redirect($sl->getCreateAccountUrl($request));
    }
}
