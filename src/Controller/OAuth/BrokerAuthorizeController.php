<?php
/*
 Copyright (C) AC SOFTWARE SP. ZO.O.
 */

namespace App\Controller\OAuth;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The request listener performs synchronous OAuth validation and serves the
 * SPA for valid GET requests before this controller is reached. This route
 * exists only to retire stale form submissions safely.
 */
class BrokerAuthorizeController {
    /** @Route("/oauth/v2/auth", name="fos_oauth_server_authorize", methods={"GET", "POST"}) */
    public function authorizeAction(Request $request): Response {
        if ($request->isMethod(Request::METHOD_POST)) {
            return new Response('This OAuth form is no longer supported. Restart authorization from the application.', Response::HTTP_GONE, [
                'Cache-Control' => 'no-store',
            ]);
        }

        // A valid GET should already have been answered by the preflight
        // listener. Do not fall through to FOS's session-backed controller.
        return new Response('OAuth authorization is temporarily unavailable. Please try again later.', Response::HTTP_SERVICE_UNAVAILABLE, [
            'Cache-Control' => 'no-store',
        ]);
    }
}
