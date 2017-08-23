<?php
/*
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

use SuplaBundle\Entity\ClientApp;

abstract class SuplaServer {
    public function __destruct() {
        $this->disconnect();
    }

    abstract protected function connect();

    abstract protected function disconnect();

    abstract protected function command($command);

    public function oauthAuthorize($userId, $accessToken) {
        $result = false;
        if ($this->connect() !== false) {
            $result = $this->command("OAUTH:" . $accessToken);
        }
        return $result !== false && preg_match("/^AUTH_OK:" . $userId . "\n/", $result) === 1 ? true : false;
    }

    private function isDeviceConnected(int $userId, int $deviceId): bool {
        $deviceId = intval($deviceId);
        if ($userId == 0 || $deviceId == 0) {
            return false;
        }
        $result = $this->command("IS-IODEV-CONNECTED:" . $userId . "," . $deviceId);
        return $result !== false && preg_match("/^CONNECTED:" . $deviceId . "\n/", $result) === 1 ? true : false;
    }

    public function checkDevicesConnection(int $userId, array $ids) {
        $result = [];
        if ($userId != 0 && $this->connect() !== false) {
            foreach ($ids as $id) {
                if ($this->isDeviceConnected($userId, $id) === true) {
                    $result[] = $id;
                }
            }
            $this->disconnect();
        }
        return $result;
    }

    private function isClientAppConnected(ClientApp $clientApp): bool {
        return !!(rand() % 2); // TODO
    }

    /**
     * @param ClientApp[] $clientApps
     * @return ClientApp[] connected client apps
     */
    public function getOnlyConnectedClientApps(array $clientApps): array {
        if ($this->connect() !== false) {
            return array_values(array_filter($clientApps, function (ClientApp $clientApp) {
                return $this->isClientAppConnected($clientApp);
            }));
        }
        return [];
    }

    public function reconnect($userId) {
        $userId = intval($userId);
        if ($userId != 0 && $this->connect() !== false) {
            $result = $this->command("USER-RECONNECT:" . $userId);
            return $result !== false && preg_match("/^OK:" . $userId . "\n/", $result) === 1 ? true : false;
        }
        return false;
    }

    public function clientReconnect(ClientApp $clientApp) {
        return $this->reconnect($clientApp->getUser()->getId()); // TODO
    }

    private function getRawValue($type, $userId, $deviceId, $channelId) {
        $userId = intval($userId, 0);
        $deviceId = intval($deviceId, 0);
        $channelId = intval($channelId, 0);
        if ($userId != 0
            && $deviceId != 0
            && $channelId != 0
            && $this->connect() !== false
        ) {
            $result = $this->command("GET-" . $type . "-VALUE:" . $userId . "," . $deviceId . "," . $channelId);

            if ($result !== false
                && preg_match("/^VALUE:/", $result) === 1
            ) {
                return $result;
            }
        }
        return false;
    }

    private function setValue(string $type, int $userId, int $deviceId, int $channelId, $value) {
        if ($userId && $deviceId && $channelId && $this->connect()) {
            $result = $this->command("SET-$type-VALUE:$userId,$deviceId,$channelId,$value");
            if ($result && preg_match("/^OK:/", $result) === 1) {
                return true;
            }
        }
        return false;
    }

    private function getValue($type, $userId, $deviceId, $channelId) {
        $result = $this->getRawValue($type, $userId, $deviceId, $channelId);
        if ($result !== false) {
            list($val) = sscanf($result, "VALUE:%f\n");

            if (is_numeric($val)) {
                return $val;
            };
        }
        return false;
    }

    public function getCharValue($userId, $deviceId, $channelId) {
        return $this->getValue('CHAR', $userId, $deviceId, $channelId);
    }

    public function getDoubleValue($userId, $deviceId, $channelId) {
        return $this->getValue('DOUBLE', $userId, $deviceId, $channelId);
    }

    public function getTemperatureValue($userId, $deviceId, $channelId) {
        return $this->getValue('TEMPERATURE', $userId, $deviceId, $channelId);
    }

    public function getHumidityValue($userId, $deviceId, $channelId) {
        return $this->getValue('HUMIDITY', $userId, $deviceId, $channelId);
    }

    public function getDistanceValue($userId, $deviceId, $channelId) {
        return $this->getValue('DOUBLE', $userId, $deviceId, $channelId);
    }

    public function getDepthValue($userId, $deviceId, $channelId) {
        return $this->getValue('DOUBLE', $userId, $deviceId, $channelId);
    }

    public function getRgbwValue($userId, $deviceId, $channelId) {
        $value = $this->getRawValue('RGBW', $userId, $deviceId, $channelId);
        if ($value !== false) {
            list($color, $color_brightness, $brightness) = sscanf($value, "VALUE:%i,%i,%i\n");
            if (is_numeric($color)
                && is_numeric($color_brightness)
                && is_numeric($brightness)
            ) {
                return ['color' => sprintf('0x%06X', $color), 'color_brightness' => $color_brightness, 'brightness' => $brightness];
            }
        }
        return false;
    }

    public function setCharValue(int $userId, int $deviceId, int $channelId, $char) {
        $char = intval($char, 0);
        if ($char < 0 || $char > 255) {
            $char = 0;
        }
        return $this->setValue('CHAR', $userId, $deviceId, $channelId, $char);
    }

    public function setRgbwValue($userId, $deviceId, $channelId, $color, $colorBrightness, $brightness) {

        $color = intval($color, 0x00FF00);
        $colorBrightness = intval($colorBrightness, 0);
        $brightness = intval($brightness, 0);

        if ($colorBrightness < 0 || $colorBrightness > 255) {
            $colorBrightness = 0;
        }

        if ($brightness < 0 || $brightness > 255) {
            $brightness = 0;
        }

        if ($color < 0 || $color > 0xffffff) {
            $color = 0x00FF00;
        }

        return $this->setValue('RGBW', $userId, $deviceId, $channelId, $color . ',' . $colorBrightness . ',' . $brightness);
    }
}
