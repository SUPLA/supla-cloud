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

namespace SuplaApiBundle\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use SuplaApiBundle\Entity\ApiUser;
use SuplaBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Each entity controller must extends this class.
 */
abstract class RestController extends FOSRestController {
    /** @deprecated use getUser() */
    public function getParentUser() {
        return $this->getUser();
    }

    /**
     * @return null|User
     */
    protected function getUser() {
        $user = parent::getUser();
        if ($user instanceof ApiUser) {
            return $user->getParentUser();
        } else {
            return $user;
        }
    }

    protected function setSerializationGroups(View $view, Request $request, array $allowedGroups): Context {
        $context = new Context();
        $include = $request->get('include', '');
        $requestedGroups = array_filter(array_map('trim', explode(',', $include)));
        $filteredGroups = array_intersect($requestedGroups, $allowedGroups);
        if (count($filteredGroups) < count($requestedGroups)) {
            $notSupported = implode(', ', array_diff($requestedGroups, $filteredGroups));
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'The following includes are not supported: ' . $notSupported);
        }
        $filteredGroups[] = 'basic';
        $context->setGroups(array_unique($filteredGroups));
        $view->setContext($context);
        return $context;
    }
}
