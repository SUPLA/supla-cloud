<?php
namespace SuplaApiBundle\DependencyInjection;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\FormAuthenticationEntryPoint;

class WebApiAuthenticationEntryPoint extends FormAuthenticationEntryPoint {
    /**
     * @inheritdoc
     */
    public function start(Request $request, AuthenticationException $authException = null) {
        if (strpos($request->getUri(), '/web-api/') !== false) {
            return new Response('', Response::HTTP_UNAUTHORIZED);
        } else {
            return parent::start($request, $authException);
        }
    }
}
