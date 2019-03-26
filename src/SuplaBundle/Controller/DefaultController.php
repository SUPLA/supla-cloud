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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller {
    /**
     * @Route("/auth/create", name="_auth_create")
     * @Route("/account/create", name="_account_create")
     * @Route("/account/create_here/{locale}", name="_account_create_here")
     */
    public function createAction(Request $request, $locale = null) {
        if ($this->getUser()) {
            return $this->redirectToRoute('_homepage');
        }
        return $this->redirectToRoute('_register', ['lang' => $request->getLocale()]);
    }

    /**
     * @Route("/", name="_homepage")
     * @Route("/register", name="_register")
     * @Route("/auth/login", name="_obsolete_login")
     * @Route("/{suffix}", requirements={"suffix"="^(?!api|oauth/|direct/).*"}, methods={"GET"})
     * @Template()
     * @Cache(expires="2016-01-01")
     */
    public function spaBoilerplateAction($suffix = null) {
        if ($suffix && preg_match('#\..{2,4}$#', $suffix)) {
            throw new NotFoundHttpException("$suffix file could not be found");
        }
    }

    /**
     * @Route("/api-docs", methods={"GET"})
     * @Template()
     */
    public function apiDocsAction() {
        return ['supla_url' => $this->container->getParameter('supla_url')];
    }

    /**
     * @Route("/api-docs/supla-api-docs.yaml", methods={"GET"})
     */
    public function getApiDocsSchemaAction() {
        $yaml = file_get_contents(\AppKernel::ROOT_PATH . '/config/supla-api-docs.yaml');
        $suplaDomain = $this->container->getParameter('supla_url');
        $yaml = str_replace('https://cloud.supla.org', $suplaDomain, $yaml);
        return new Response($yaml, Response::HTTP_OK, ['Content-Type' => 'application/yaml']);
    }

    /**
     * @Route("/api-docs/oauth2-redirect.html", methods={"GET"})
     * @Template()
     */
    public function apiDocsOAuth2RedirectAction() {
    }
}
