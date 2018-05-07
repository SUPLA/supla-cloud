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
        $new_account_server_list,
        $suplaProtocol
    ) {

        $this->router = $router;
        $this->user_manager = $user_manager;
        $this->server = $supla_server;
        $this->na_servers = $new_account_server_list;
        $this->autodiscover = $autodiscover;
        $this->suplaProtocol = $suplaProtocol;
    }

    public function userExists($username, &$remote_server) {

        if (strlen(@$username) == 0) {
            return false;
        }

        $user = $this->user_manager->userByEmail($username);

        if ($user != null) {
            return true;
        }

        if ($this->autodiscover->enabled()) {
            $exists = $this->autodiscover->findServer($username);
            return $exists ? true : $exists;
        }

        return false;
    }

    public function getAuthServerForUser(Request $request, $username) {
        $result = false;
        $protocol = 'https';

        $local = $request->getHost();
        if ($request->getPort() != 443) {
            $local .= ":" . $request->getPort();
        }

        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $user = $this->user_manager->userByEmail($username);

            if ($user !== null) {
                $result = $local;
                $protocol = $this->suplaProtocol;
            } elseif ($this->autodiscover->enabled()) {
                $result = $this->autodiscover->findServer($username);
                if ($result === null) {
                    return false;
                }
            }
        }

        if (!$result) {
            $result = $local;
            $protocol = $this->suplaProtocol;
        }

        return $protocol . '://' . $result;
    }

    public function getCreateAccountUrl(Request $request) {
        if ($this->na_servers && count($this->na_servers) > 0) {
            $server = $this->na_servers[rand(0, count($this->na_servers) - 1)];
            if (strlen(@$server) > 0) {
                return 'https://' . $server
                    . $this->router->generate('_register', ['lang' => $request->getLocale()]);
            }
        };
        return $request->getScheme() . '://' . $request->getHost()
            . $this->router->generate('_register', ['lang' => $request->getLocale()]);
    }

    public function getAutodiscover(): SuplaAutodiscover {
        return $this->autodiscover;
    }
}
