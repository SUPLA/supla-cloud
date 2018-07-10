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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SuplaApiBundle\Model\Audit\FailedAuthAttemptsUserBlocker;
use SuplaBundle\Supla\ServerList;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller {
    /** @var FailedAuthAttemptsUserBlocker */
    private $failedAuthAttemptsUserBlocker;
    /** @var ServerList */
    private $serverList;

    public function __construct(FailedAuthAttemptsUserBlocker $failedAuthAttemptsUserBlocker, ServerList $serverList) {
        $this->failedAuthAttemptsUserBlocker = $failedAuthAttemptsUserBlocker;
        $this->serverList = $serverList;
    }

    /**
     * @Route("/auth/create", name="_auth_create")
     * @Route("/account/create", name="_account_create")
     * @Route("/account/create_here/{locale}", name="_account_create_here")
     */
    public function createAction(Request $request, $locale = null) {
        if ($this->getUser()) {
            return $this->redirectToRoute('_homepage');
        }
        return $this->redirect($this->serverList->getCreateAccountUrl($request));
    }

    /**
     * @Route("/", name="_homepage")
     * @Route("/register", name="_register")
     * @Route("/auth/login", name="_obsolete_login")
     * @Route("/{suffix}", requirements={"suffix"="^(?!api|oauth/).*"}, methods={"GET"})
     * @Template()
     */
    public function spaBoilerplateAction($suffix = null) {
        if ($suffix && preg_match('#\..{2,4}$#', $suffix)) {
            throw new NotFoundHttpException("$suffix file could not be found");
        }
    }
}
