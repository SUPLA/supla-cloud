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

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Model\CurrentUserAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Each entity controller must extends this class.
 *
 * @method User|null getUser()
 */
abstract class RestController extends AbstractFOSRestController {
    use CurrentUserAware;

    protected function getDefaultAllowedSerializationGroups(Request $request): array {
        return [];
    }

    protected function setSerializationGroups(View $view, Request $request, $allowedGroups = null, array $extraGroups = []): Context {
        if ($allowedGroups === null) {
            $allowedGroups = $this->getDefaultAllowedSerializationGroups($request);
        }
        $context = new Context();
        $include = $request->get('include', '');
        $requestedGroups = array_filter(array_map('trim', explode(',', $include)));
        $requestedGroups = array_values(array_unique($requestedGroups));
        $filteredGroups = array_intersect($requestedGroups, $allowedGroups);
        if (count($filteredGroups) < count($requestedGroups)) {
            $notSupported = implode(', ', array_diff($requestedGroups, $filteredGroups));
            $supported = implode(', ', $allowedGroups);
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                vsprintf('The following includes are not supported: %s. Available: %s.', [$notSupported, $supported])
            );
        }
        $filteredGroups[] = 'basic';
        $filteredGroups = array_merge($filteredGroups, $extraGroups);
        $desiredGroups = array_values(array_unique($filteredGroups));
        foreach ($allowedGroups as $group => $translation) {
            if (!is_int($group) && ($index = array_search($group, $desiredGroups)) !== false) {
                $desiredGroups[$index] = $translation;
            }
        }
        $context->setGroups($desiredGroups);
        $view->setContext($context);
        return $context;
    }

    protected function serializedView($data, Request $request, array $extraSerializationGroups = [], int $statusCode = Response::HTTP_OK) {
        $view = $this->view($data, $statusCode);
        $this->setSerializationGroups($view, $request, null, $extraSerializationGroups);
        return $view;
    }
}
