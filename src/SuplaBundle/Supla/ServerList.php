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

namespace SuplaBundle\Supla;

use SuplaBundle\Controller\AjaxController;
use SuplaBundle\Model\UserManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;

class ServerList {
    protected $server = null;
    protected $servers = null;
    protected $user_manager = null;
    protected $router = null;
    protected $autodiscover = null;
    private $suplaProtocol;

    public function __construct(
        Router $router,
        UserManager $user_manager,
        SuplaAutodiscover $autodiscover,
        $supla_server,
        $supla_server_list,
        $suplaProtocol
    ) {

        $this->router = $router;
        $this->user_manager = $user_manager;
        $this->server = $supla_server;
        $this->servers = $supla_server_list;
        $this->autodiscover = $autodiscover;
        $this->suplaProtocol = $suplaProtocol;

        if (count(@$servers) > 1) {
            for ($a = 0; $a < count($servers); $a++) {
                if ($servers[$a]['address'] === $server) {
                    if ($a > 0) {
                        $s = $servers[$a];
                        $servers[$a] = $servers[0];
                        $servers[0] = $s;
                    }

                    break;
                }
            }
        }
    }

    public function requestAllowed() {

        $addr = @$_SERVER['REMOTE_ADDR'];

        if ($this->servers != null) {
            foreach ($this->servers as $server) {
                if (@$_SERVER['REMOTE_ADDR'] == $server['ip']) {
                    return true;
                }
            }
        };

        return false;
    }

    public function userExists($username, &$remote_server) {

        if (strlen(@$username) == 0) {
            return false;
        }

        $err = false;
        $user = $this->user_manager->userByEmail($username);

        if ($user != null) {
            return true;
        }

        if ($this->autodiscover->findServer($username)) {
            return true;
        }

        if ($this->servers != null) {
            foreach ($this->servers as $svr) {
                if ($svr['address'] !== $this->server) {
                    $rr = AjaxController::remoteRequest(
                        'https://' . $svr['address'] . $this->router->generate('_account_ajax_user_exists'),
                        ["username" => $username]
                    );

                    if ($rr != null
                        && $rr !== false
                        && @$rr->success == true
                    ) {
                        if (@$rr->exists == true) {
                            $remote_server = $svr['address'];
                            return true;
                        }
                    } else {
                        $err = true;
                        $remote_server = $svr['address'];
                    }
                }
            }
        }

        return $err === true ? null : false;
    }

    public function getAuthServerForUser(Request $request, $username) {
        $result = false;
        $err = false;
        $protocol = 'https';

        if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
            return $result;
        }

        $local = $request->getHost();
        if ($request->getPort() != 443) {
            $local .= ":" . $request->getPort();
        }

        $user = $this->user_manager->userByEmail($username);

        if ($user !== null) {
            $result = $local;
            $protocol = $this->suplaProtocol;
        } else {
            $result = $this->autodiscover->findServer($username);
        }

        if (!$result && $this->servers != null) {
            foreach ($this->servers as $svr) {
                if ($svr['address'] !== $this->server) {
                    $rr = AjaxController::remoteRequest(
                        'https://' . $svr['address'] . $this->router->generate('_account_ajax_user_exists'),
                        ["username" => $username]
                    );

                    if ($rr != null
                        && $rr !== false
                        && @$rr->success == true
                    ) {
                        if (@$rr->exists == true) {
                            $result = $svr['address'];
                            break;
                        }
                    } else {
                        $err = true;
                    }
                }
            }
        }

        if ($err && !$result) {
            return false;
        }

        if (!$result) {
            $result = $local;
            $protocol = $this->suplaProtocol;
        }

        return $protocol . '://' . $result;
    }

    public function getCreateAccountUrl(Request $request) {

        if (count(@$this->servers) < 2) {
            return $request->getScheme() . '://' . $request->getHost()
                . $this->router->generate('_account_create_here_lc', ['locale' => $request->getLocale()]);
        }

        $avil = [];
        foreach ($this->servers as $server) {
            if ($server['create_account'] === true) {
                $avil[] = $server;
            }
        }

        if (count($avil) > 0) {
            $server = $avil[rand(0, count($avil) - 1)];

            if (strlen(@$server['address']) > 0) {
                return 'https://' . $server['address']
                    . $this->router->generate('_account_create_here_lc', ['locale' => $request->getLocale()]);
            }
        }

        return $request->getScheme() . '://' . $request->getHost() . $this->router->generate('_temp_unavailable');
    }
}
