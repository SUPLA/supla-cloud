<?php
/*
 src/SuplaBundle/Supla/ServerCtrl.php

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
// @codingStandardsIgnoreFile
namespace SuplaBundle\Supla;

class SuplaServerReal {
    private $socket = false;

    function __destruct() {
        $this->disconnect();
    }

    private function connect() {

        if ($this->socket !== false) {
            return $this->socket;
        }

        $old_er = error_reporting();
        error_reporting($old_er ^ E_WARNING);
        $this->socket = stream_socket_client('unix:///tmp/supla-server-ctrl.sock', $errno, $errstr);
        error_reporting($old_er);

        if ($this->socket === false || $this->socket === null) {
            $this->socket == false;
            return false;
        }

        $hello = fread($this->socket, 4096);

        if (preg_match("/^SUPLA SERVER CTRL\n/", $hello) !== 1) {
            $this->disconnect();
        }

        return $this->socket;
    }

    public function disconnect() {

        if ($this->socket !== false) {
            fclose($this->socket);
            $this->socket = false;
        }
    }

    private function command($cmd) {

        if ($this->socket !== false) {
            fwrite($this->socket, $cmd . "\n");
            $result = fread($this->socket, 4096);

            return $result;
        }

        return false;
    }

    public function oauth_authorize($user_id, $access_token) {

        $result = false;

        if ($this->connect() !== false) {
            $result = $this->command("OAUTH:" . $access_token);
        }

        return $result !== false && preg_match("/^AUTH_OK:" . $user_id . "\n/", $result) === 1 ? true : false;
    }

    private function _iodevice_connected($user_id, $iodev_id) {

        $iodev_id = intval($iodev_id, 0);

        if ($user_id == 0 || $iodev_id == 0) {
            return false;
        }

        $result = $this->command("IS-IODEV-CONNECTED:" . $user_id . "," . $iodev_id);

        return $result !== false && preg_match("/^CONNECTED:" . $iodev_id . "\n/", $result) === 1 ? true : false;
    }

    function iodevice_connected($user_id, $ids = 0) {

        $result = [];
        $user_id = intval($user_id, 0);

        if ($user_id != 0 && $this->connect() !== false) {
            if (!is_array($ids) && is_int($ids)) {
                $ids = [$ids];
            }

            if (is_array($ids)) {
                foreach ($ids as $id) {
                    if ($this->_iodevice_connected($user_id, $id) === true) {
                        $result[] = $id;
                    }
                }
            }

            $this->disconnect();
        }

        return $result;
    }

    function reconnect($user_id) {

        $user_id = intval($user_id, 0);

        if ($user_id != 0 && $this->connect() !== false) {
            $result = $this->command("USER-RECONNECT:" . $user_id);
            return $result !== false && preg_match("/^OK:" . $user_id . "\n/", $result) === 1 ? true : false;
        }

        return false;
    }

    private function __get_value($type, $user_id, $iodev_id, $channel_id) {

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

    private function set_value($type, $user_id, $iodev_id, $channel_id, $value) {

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

    private function get_value($type, $user_id, $iodev_id, $channel_id) {

        $result = $this->__get_value($type, $user_id, $iodev_id, $channel_id);

        if ($result !== false) {
            list($val) = sscanf($result, "VALUE:%f\n");

            if (is_numeric($val)) {
                return $val;
            };
        }

        return false;
    }

    function get_char_value($user_id, $iodev_id, $channel_id) {
        return $this->get_value('CHAR', $user_id, $iodev_id, $channel_id);
    }

    function get_double_value($user_id, $iodev_id, $channel_id) {
        return $this->get_value('DOUBLE', $user_id, $iodev_id, $channel_id);
    }

    function get_temperature_value($user_id, $iodev_id, $channel_id) {
        return $this->get_value('TEMPERATURE', $user_id, $iodev_id, $channel_id);
    }

    function get_humidity_value($user_id, $iodev_id, $channel_id) {
        return $this->get_value('HUMIDITY', $user_id, $iodev_id, $channel_id);
    }

    function get_distance_value($user_id, $iodev_id, $channel_id) {
        return $this->get_value('DOUBLE', $user_id, $iodev_id, $channel_id);
    }

    function get_depth_value($user_id, $iodev_id, $channel_id) {
        return $this->get_value('DOUBLE', $user_id, $iodev_id, $channel_id);
    }

    function get_rgbw_value($user_id, $iodev_id, $channel_id) {

        $value = $this->__get_value('RGBW', $user_id, $iodev_id, $channel_id);

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

    function set_char_value($user_id, $iodev_id, $channel_id, $char) {

        $char = intval($char, 0);

        if ($char < 0 || $char > 255) {
            $char = 0;
        }

        return $this->set_value('CHAR', $user_id, $iodev_id, $channel_id, $char);
    }

    function set_rgbw_value($user_id, $iodev_id, $channel_id, $color, $color_brightness, $brightness) {

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

        return $this->set_value('RGBW', $user_id, $iodev_id, $channel_id, $color . ',' . $color_brightness . ',' . $brightness);
    }
}
