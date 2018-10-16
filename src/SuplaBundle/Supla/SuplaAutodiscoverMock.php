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

class SuplaAutodiscoverMock extends SuplaAutodiscover {
    const SECONDARY_INSTANCE = 'localhost:81';

    public function __construct(UserManager $userManager) {
        parent::__construct(self::SECONDARY_INSTANCE ? 'mocked-autodiscover' : false, 'http', 'http://supla.local', $userManager);
    }

    protected function remoteRequest($endpoint, $post = false) {
        if (preg_match('#/users/(.+)#', $endpoint, $match)) {
            $server = $this->getServerForUsername(urldecode($match[1]));
            if ($server) {
                return ['server' => $server];
            }
        } elseif (preg_match('#/new-account-server/#', $endpoint)) {
            return ['server' => self::SECONDARY_INSTANCE];
        } elseif (preg_match('#/mapped-client-id/(.+)/(.+)#', $endpoint)) {
            return ['mapped_client_id' => '2_19fmbgwtxl8ko40wgcscwg088c4wow4cw4g4ckgcsc08g088c0'];
        }
        return false;
    }

    private function getServerForUsername($username) {
        if (strpos($username, 'user2')) {
            return self::SECONDARY_INSTANCE;
        }
    }
}
