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

use SuplaBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class AbstractController extends Controller {
    protected function expectsJsonResponse(): bool {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        return in_array('application/json', $request->getAcceptableContentTypes());
    }

    /** @return JsonResponse */
    protected function jsonResponse($responseData, $serializationGroups = 'basic', int $status = 200) {
        if (!is_array($serializationGroups)) {
            $serializationGroups = [$serializationGroups];
        }
        $serialized = $this->get('serializer')->serialize($responseData, 'json', ['groups' => $serializationGroups]);
        $response = new JsonResponse($serialized, $status, [], true);
        // prevent caching of JSON responses, see https://github.com/SUPLA/supla-cloud/issues/91
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('max-age', 0);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->headers->addCacheControlDirective('no-store', true);
        return $response;
    }

    /** @return User */
    protected function getUser() {
        return parent::getUser();
    }
}
