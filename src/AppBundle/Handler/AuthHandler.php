<?php
/*
 src/AppBundle/Handler/AuthHandler.php

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

namespace AppBundle\Handler;

use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * @author Przemyslaw Zygmunt p.zygmunt@acsoftware.pl [AC SOFTWARE]
 */
class AuthHandler extends ContainerAware implements AuthenticationSuccessHandlerInterface
{

	function onAuthenticationSuccess(Request $request, TokenInterface $token)
	{
		$user = $token->getUser();
		$user->setLastLogin(new \DateTime());
		$user->setLastIpv4($this->container->get('request')->getClientIp());
		$this->container->get('doctrine')->getEntityManager()->flush();
		
		return new RedirectResponse($this->container->get('router')->generate('_homepage'));
	}
}

?>