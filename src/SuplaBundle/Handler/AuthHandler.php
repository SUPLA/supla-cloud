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

namespace SuplaBundle\Handler;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthHandler implements AuthenticationSuccessHandlerInterface {

    protected $entityManager = null;
    protected $router = null;

    public function __construct(EntityManager $entityManager, Router $router) {
        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token) {
        $user = $token->getUser();
        $user->moveCurrentLoginToLastLogin();
        $user->setCurrentLogin(new \DateTime());
        $user->setCurrentIpv4($request->getClientIp());
        $this->entityManager->flush();
        return new RedirectResponse($this->router->generate('_homepage'));
    }
}
