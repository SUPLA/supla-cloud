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

use SuplaBundle\Entity\ClientApp;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Model\LocalSuplaCloud;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

abstract class SuplaServer {
    use CurrentUserAware;

    /** @var string */
    protected $socketPath;
    /** @var LocalSuplaCloud */
    private $localSuplaCloud;

    public function __construct(string $socketPath, LocalSuplaCloud $localSuplaCloud) {
        $this->socketPath = $socketPath;
        $this->localSuplaCloud = $localSuplaCloud;
    }

    public function __destruct() {
        $this->disconnect();
    }

    abstract protected function connect();

    abstract protected function disconnect();

    abstract protected function command($command);

    private function isConnected(int $userId, int $id, $what = 'iodev'): bool {
        $userId = intval($userId);
        $id = intval($id);

        if ($userId == 0 || $id == 0) {
            return false;
        }

        switch ($what) {
            case 'client':
                $what = 'CLIENT';
                break;
            default:
                $what = 'IODEV';
                break;
        }

        $result = $this->command("IS-" . $what . "-CONNECTED:" . $userId . "," . $id);
        return $result !== false && preg_match("/^CONNECTED:" . $id . "\n/", $result) === 1 ? true : false;
    }

    public function checkDevicesConnection(int $userId, array $ids) {
        $result = [];
        if ($userId != 0 && $this->connect() !== false) {
            foreach ($ids as $id) {
                if ($this->isConnected($userId, $id) === true) {
                    $result[] = $id;
                }
            }
            $this->disconnect();
        }
        return $result;
    }

    public function isClientAppConnected(ClientApp $clientApp): bool {
        if ($this->connect() !== false) {
            return $this->isConnected($clientApp->getUser()->getId(), $clientApp->getId(), 'client');
        }
        return false;
    }

    public function userAction($userId, $action) {
        if (!$userId) {
            $user = $this->getCurrentUserOrThrow();
            $userId = $user->getId();
        }
        $userId = intval($userId);
        if ($userId != 0 && $this->connect() !== false) {
            $result = $this->command("USER-".$action.":" . $userId);
            return $result !== false && preg_match("/^OK:" . $userId . "\n/", $result) === 1 ? true : false;
        }
        return false;
    }

    public function reconnect($userId = null) {
        return $this->userAction($userId, "RECONNECT");
    }

    public function amazonAlexaCredentialsChanged($userId = null) {
        return $this->userAction($userId, "ALEXA-CREDENTIALS-CHANGED");
    }

    public function googleHomeLinkChanged($userId = null) {
        return $this->userAction($userId, "GOOGLE-HOME-LINK-CHANGED");
    }

    public function onOAuthClientRemoved($userId = null) {
        $this->amazonAlexaCredentialsChanged($userId);
    }

    public function onDeviceDeleted($userId = null) {
        return $this->userAction($userId, "ON-DEVICE-DELETED");
    }

    public function clientReconnect(ClientApp $clientApp) {
        if ($this->connect() !== false) {
            $result = $this->command("CLIENT-RECONNECT:" . $clientApp->getUser()->getId() . "," . $clientApp->getId());
            return $result !== false && preg_match("/^OK:" . $clientApp->getId() . "\n/", $result) === 1 ? true : false;
        }
        return false;
    }

    private function getRawValue($type, IODeviceChannel $channel) {
        if ($this->connect() !== false) {
            $args = [$channel->getUser()->getId(), $channel->getIoDevice()->getId(), $channel->getId()];
            $result = $this->command("GET-" . $type . "-VALUE:" . implode(',', $args));

            if ($result !== false && preg_match("/^VALUE:/", $result) === 1) {
                return $result;
            }
        }
        return false;
    }

    private function getValue($type, IODeviceChannel $channel) {
        $result = $this->getRawValue($type, $channel);
        if ($result !== false) {
            list($val) = sscanf($result, "VALUE:%f\n");

            if (is_numeric($val)) {
                return $val;
            };
        }
        return false;
    }

    /** @return int|bool */
    public function getIntValue(IODeviceChannel $channel) {
        $value = $this->getValue('CHAR', $channel);
        return is_numeric($value) ? intval($value) : $value;
    }

    public function getCharValue(IODeviceChannel $channel) {
        return $this->getValue('CHAR', $channel);
    }

    public function getTemperatureValue(IODeviceChannel $channel) {
        return $this->getValue('TEMPERATURE', $channel);
    }

    public function getHumidityValue(IODeviceChannel $channel) {
        return $this->getValue('HUMIDITY', $channel);
    }

    public function getDistanceValue(IODeviceChannel $channel) {
        return $this->getValue('DOUBLE', $channel);
    }

    public function getRgbwValue(IODeviceChannel $channel) {
        $value = $this->getRawValue('RGBW', $channel);
        if ($value !== false) {
            list($color, $color_brightness, $brightness) = sscanf($value, "VALUE:%i,%i,%i\n");
            if (is_numeric($color) && is_numeric($color_brightness) && is_numeric($brightness)) {
                return ['color' => sprintf('0x%06X', $color), 'color_brightness' => $color_brightness, 'brightness' => $brightness];
            }
        }
        return false;
    }

    public function executeSetCommand(string $command) {
        if ($this->connect()) {
            $result = $this->command($command);
            if (!$result || preg_match("/^OK:/", $result) !== 1) {
                throw new ServiceUnavailableHttpException(30, 'SUPLA Server was unable to execute the action.');
            }
        } else {
            throw new ServiceUnavailableHttpException(10, 'SUPLA Server is down.');
        }
    }

    public function isAlive(): bool {
        $server = $this->localSuplaCloud->getHost(false);
        if (!$server) {
            $server = "localhost";
        }
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
        $socket = @stream_socket_client("tls://" . $server . ":2016", $errno, $errstr, 3, STREAM_CLIENT_CONNECT, $context);
        if ($socket) {
            fclose($socket);
        } else {
            return false;
        }
        $socket = @stream_socket_client($server . ":2015", $errno, $errstr, 3);
        if ($socket) {
            fclose($socket);
        } else {
            return false;
        }
        if ($this->connect() !== false) {
            $result = $this->command("UNKNOWN-COMMAND");
            return $result !== false && preg_match("/^COMMAND_UNKNOWN\n/", $result) === 1 ? true : false;
        }
        return false;
    }
}
