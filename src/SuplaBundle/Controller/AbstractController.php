<?php
namespace SuplaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class AbstractController extends Controller {
    protected function expectsJsonResponse(): bool {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        return in_array('application/json', $request->getAcceptableContentTypes());
    }

    protected function jsonResponse($responseData, $serializationGroups = 'basic', int $status = 200): JsonResponse {
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
}
