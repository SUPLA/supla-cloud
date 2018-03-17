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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller {
// @codingStandardsIgnoreStart
    private function _getBaseAidLid(&$aid, &$loc, $aid_enabled, $loc_enabled) {
// @codingStandardsIgnoreEnd
        $aid = null;
        $loc = null;

        $user = $this->get('security.token_storage')->getToken()->getUser();

        for ($a = 0; $a < $user->getAccessIds()->count(); $a++) {
            if ($aid == null
                && ($aid_enabled === false
                    || $user->getAccessIds()->get($a)->getEnabled() === true)
            ) {
                $aid = $user->getAccessIds()->get($a);
                break;
            }
        }

        if ($aid != null) {
            for ($a = 0; $a < $aid->getLocations()->count(); $a++) {
                if ($loc == null
                    && ($loc_enabled === false
                        || $aid->getLocations()->get($a)->getEnabled() === true)
                ) {
                    $loc = $aid->getLocations()->get($a);
                    break;
                }
            }
        }

        return $aid !== null && $loc !== null;
    }

    private function getBaseAidLid(&$aid, &$loc) {

        $aid = null;
        $loc = null;

        if ($this->_getBaseAidLid($aid, $loc, true, true)
            || $this->_getBaseAidLid($aid, $loc, true, false)
            || $this->_getBaseAidLid($aid, $loc, false, true)
            || $this->_getBaseAidLid($aid, $loc, false, false)
        ) {
            return true;
        };

        return false;
    }

    /**
     * @Route("", name="_homepage")
     */
    public function indexAction(Request $request) {

        $show_base_settings = false;
        $base_server = '';
        $base_accessid = '';
        $base_accesspwd = '';
        $base_locid = '';
        $base_locpwd = '';

        $viewed = $this->get('session')->get('hompage_viewed') == '1' ? true : false;
        $this->get('session')->set('hompage_viewed', '1');

        $aid = null;
        $loc = null;

        if ($this->getBaseAidLid($aid, $loc)) {
            $base_accessid = $aid->getId();
            $base_accesspwd = $aid->getPassword();
            $base_locid = $loc->getId();
            $base_locpwd = $loc->getPassword();
            $base_server = $this->container->getParameter('supla_server');
            $show_base_settings = true;
        }

        return $this->render(
            'SuplaBundle:Default:index.html.twig',
            ['show_base_settings' => $show_base_settings,
                'base_accessid' => $base_accessid,
                'base_accesspwd' => $base_accesspwd,
                'base_locid' => $base_locid,
                'base_locpwd' => $base_locpwd,
                'base_server' => $base_server,
                'homepage_viewed' => $viewed,

            ]
        );
    }

    /**
     * @Route("me", name="_my_supla")
     * @Template
     */
    public function mySuplaAction() {
        return [];
    }

    /**
     * Redirect URLs with a Trailing Slash.
     * @see https://symfony.com/doc/current/routing/redirect_trailing_slash.html
     * @Route("/{url}", name="remove_trailing_slash", requirements={"url" = ".*\/$"}, methods={"GET"})
     */
    public function removeTrailingSlashAction(Request $request) {
        $pathInfo = $request->getPathInfo();
        $requestUri = $request->getRequestUri();
        $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);
        return $this->redirect($url, 301);
    }
}
