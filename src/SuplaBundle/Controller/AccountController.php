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
use SuplaBundle\Model\Transactional;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/account")
 */
class AccountController extends Controller {
    use Transactional;

    /**
     * @Route("/confirmemail/{token}", name="_account_confirmemail")
     */
    public function confirmEmailAction($token) {
        $user_manager = $this->get('user_manager');

        if (($user = $user_manager->confirm($token)) !== null) {
            $mailer = $this->get('supla_mailer');
            $mailer->sendActivationEmailMessage($user);

            $this->get('session')->getFlashBag()->add('success', [
                'title' => 'Success',
                'message' => 'Account has been activated. You can Sign In now.',
            ]);
        } else {
            $this->get('session')->getFlashBag()->add('error', ['title' => 'Error', 'message' => 'Token does not exist']);
        }

        return $this->redirectToRoute("_auth_login");
    }

    /**
     * @Route("/create", name="_account_create")
     */
    public function createAction(Request $request) {
        if ($this->getUser()) {
            return $this->redirectToRoute('_homepage');
        }
        $sl = $this->get('server_list');
        return $this->redirect($sl->getCreateAccountUrl($request));
    }
}
