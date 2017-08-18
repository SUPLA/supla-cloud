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

class SuplaServerReal {
    private $socket = null;

    public function __destruct() {
        $this->disconnect();
    }

    private function connect() {
        if ($this->socket !== null) {
            return $this->socket;
        }

        $old_er = error_reporting();
        error_reporting($old_er ^ E_WARNING);
        $this->socket = stream_socket_client('unix:///tmp/supla-server-ctrl.sock', $errno, $errstr);
        error_reporting($old_er);

        if ($this->socket === null) {
            return false;
        }

        $hello = fread($this->socket, 4096);

        if (preg_match("/^SUPLA SERVER CTRL\n/", $hello) !== 1) {
            $this->disconnect();
        }
        return $this->socket;
    }

    public function disconnect() {
        if ($this->socket !== null) {
            fclose($this->socket);
            $this->socket = null;
        }
    }

    private function command($cmd) {
        if ($this->socket !== null) {
            fwrite($this->socket, $cmd . "\n");
            $result = fread($this->socket, 4096);
            return $result;
        }
        return false;
    }

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

    public function reconnect($userId) {
        $userId = intval($userId);
        if ($userId != 0 && $this->connect() !== false) {
            $result = $this->command("USER-RECONNECT:" . $userId);
            return $result !== false && preg_match("/^OK:" . $userId . "\n/", $result) === 1 ? true : false;
        }
        return false;
    }

    private function getRawValue($type, $user_id, $iodev_id, $channel_id) {
        $user_id = intval($user_id, 0);
        $iodev_id = intval($iodev_id, 0);
        $channel_id = intval($channel_id, 0);
        if ($user_id != 0
            && $iodev_id != 0
            && $channel_id != 0
            && $this->connect() !== false
        ) {
            $result = $this->command("GET-" . $type . "-VALUE:" . $user_id . "," . $iodev_id . "," . $channel_id);

            if ($result !== false
                && preg_match("/^VALUE:/", $result) === 1
            ) {
                return $result;
            }
        }
        return false;
    }

    private function setValue($type, $user_id, $iodev_id, $channel_id, $value) {

        $user_id = intval($user_id, 0);
        $iodev_id = intval($iodev_id, 0);
        $channel_id = intval($channel_id, 0);

        if ($user_id != 0
            && $iodev_id != 0
            && $channel_id != 0
            && $this->connect() !== false
        ) {
            $result = $this->command("SET-" . $type . "-VALUE:" . $user_id . "," . $iodev_id . "," . $channel_id . "," . $value);

            if ($result !== false
                && preg_match("/^OK:/", $result) === 1
            ) {
                return true;
            }
        }

        return false;
    }

    private function getValue($type, $user_id, $iodev_id, $channel_id) {
        $result = $this->getRawValue($type, $user_id, $iodev_id, $channel_id);
        if ($result !== false) {
            list($val) = sscanf($result, "VALUE:%f\n");

            if (is_numeric($val)) {
                return $val;
            };
        }
        return false;
    }

    public function getCharValue($user_id, $iodev_id, $channel_id) {
        return $this->getValue('CHAR', $user_id, $iodev_id, $channel_id);
    }

    public function getDoubleValue($user_id, $iodev_id, $channel_id) {
        return $this->getValue('DOUBLE', $user_id, $iodev_id, $channel_id);
    }

    public function getTemperatureValue($user_id, $iodev_id, $channel_id) {
        return $this->getValue('TEMPERATURE', $user_id, $iodev_id, $channel_id);
    }

    public function getHumidityValue($user_id, $iodev_id, $channel_id) {
        return $this->getValue('HUMIDITY', $user_id, $iodev_id, $channel_id);
    }

    public function getDistanceValue($user_id, $iodev_id, $channel_id) {
        return $this->getValue('DOUBLE', $user_id, $iodev_id, $channel_id);
    }

    public function getDepthValue($user_id, $iodev_id, $channel_id) {
        return $this->getValue('DOUBLE', $user_id, $iodev_id, $channel_id);
    }

    public function getRgbwValue($user_id, $iodev_id, $channel_id) {
        $value = $this->getRawValue('RGBW', $user_id, $iodev_id, $channel_id);
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

    public function setCharValue($user_id, $iodev_id, $channel_id, $char) {
        $char = intval($char, 0);
        if ($char < 0 || $char > 255) {
            $char = 0;
        }
        return $this->setValue('CHAR', $user_id, $iodev_id, $channel_id, $char);
    }

    public function setRgbwValue($user_id, $iodev_id, $channel_id, $color, $color_brightness, $brightness) {

        $color = intval($color, 0x00FF00);
        $color_brightness = intval($color_brightness, 0);
        $brightness = intval($brightness, 0);

        if ($color_brightness < 0 || $color_brightness > 255) {
            $color_brightness = 0;
        }

        if ($brightness < 0 || $brightness > 255) {
            $brightness = 0;
        }

        if ($color < 0 || $color > 0xffffff) {
            $color = 0x00FF00;
        }

        return $this->setValue('RGBW', $user_id, $iodev_id, $channel_id, $color . ',' . $color_brightness . ',' . $brightness);
    }
}
