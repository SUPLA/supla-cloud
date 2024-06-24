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

use SuplaBundle\Entity\Main\IODeviceChannel;

class SuplaOcr {
    protected SuplaBrokerHttpClient $brokerHttpClient;
    private string $suplaOcrUrl;

    public function __construct(string $ocrUrl, SuplaBrokerHttpClient $brokerHttpClient) {
        $this->suplaOcrUrl = $ocrUrl;
        if (strpos($this->suplaOcrUrl, 'http') !== 0) {
            $this->suplaOcrUrl = 'https://' . $this->suplaOcrUrl;
        }
        $this->brokerHttpClient = $brokerHttpClient;
    }

    public function getLatestImage(IODeviceChannel $channel) {
        $deviceGuid = $channel->getIoDevice()->getGUIDString();
        $fullUrl = sprintf("%s/pictures/%s/latest", $this->suplaOcrUrl, $deviceGuid);
        return $this->brokerHttpClient->request($fullUrl);
    }
}
