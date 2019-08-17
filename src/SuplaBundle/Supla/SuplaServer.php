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

use Psr\Log\LoggerInterface;
use SuplaBundle\Entity\ClientApp;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Model\LocalSuplaCloud;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

abstract class SuplaServer {
    use CurrentUserAware;

    /** @var string */
    protected $socketPath;
    /** @var LocalSuplaCloud */
    protected $localSuplaCloud;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(string $socketPath, LocalSuplaCloud $localSuplaCloud, LoggerInterface $logger) {
        $this->socketPath = $socketPath;
        $this->localSuplaCloud = $localSuplaCloud;
        $this->logger = $logger;
    }

    public function __destruct() {
        $this->disconnect();
    }

    abstract public function isAlive(): bool;

    abstract protected function connect();

    abstract protected function disconnect();

    abstract protected function command($command);

    private function executeCommand(string $command) {
        if ($this->connect() !== false) {
            $result = $this->command($command);
        } else {
            throw new ServiceUnavailableHttpException(10, 'SUPLA Server is down.');
        }
        $this->logger->debug('SuplaServer command', ['command' => $command, 'result' => $result]);
        return $result;
    }

    private function isConnected(int $userId, int $id, $what = 'iodev'): bool {
        if ($userId == 0 || $id == 0) {
            return false;
        }
        $what = $what == 'client' ? 'CLIENT' : 'IODEV';
        $result = $this->executeCommand("IS-" . $what . "-CONNECTED:" . $userId . "," . $id);
        return $result !== false && preg_match("/^CONNECTED:" . $id . "\n/", $result) === 1 ? true : false;
    }

    public function isClientAppConnected(ClientApp $clientApp): bool {
        if (!$clientApp->getEnabled()) {
            return false;
        }
        return $this->isConnected($clientApp->getUser()->getId(), $clientApp->getId(), 'client');
    }

    public function isDeviceConnected(IODevice $device) {
        if (!$device->getEnabled()) {
            return false;
        }
        return $this->isConnected($device->getUser()->getId(), $device->getId());
    }

    private function userAction($userId, $action) {
        if (!$userId) {
            $user = $this->getCurrentUserOrThrow();
            $userId = $user->getId();
        }
        $userId = intval($userId);
        if ($userId != 0) {
            $result = $this->executeCommand("USER-" . $action . ":" . $userId);
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

    public function googleHomeCredentialsChanged($userId = null) {
        return $this->userAction($userId, "GOOGLE-HOME-CREDENTIALS-CHANGED");
    }

    public function onOAuthClientRemoved($userId = null) {
        $this->amazonAlexaCredentialsChanged($userId);
    }

    public function onDeviceDeleted($userId = null) {
        return $this->userAction($userId, "ON-DEVICE-DELETED");
    }

    public function clientReconnect(ClientApp $clientApp) {
        $result = $this->executeCommand("CLIENT-RECONNECT:" . $clientApp->getUser()->getId() . "," . $clientApp->getId());
        return $result !== false && preg_match("/^OK:" . $clientApp->getId() . "\n/", $result) === 1 ? true : false;
    }

    private function getRawValue($type, IODeviceChannel $channel) {
        $args = [$channel->getUser()->getId(), $channel->getIoDevice()->getId(), $channel->getId()];
        $result = $this->executeCommand("GET-" . $type . "-VALUE:" . implode(',', $args));
        if ($result !== false && preg_match("/^VALUE:/", $result) === 1) {
            return $result;
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
                return ['color' => $color, 'color_brightness' => $color_brightness, 'brightness' => $brightness];
            }
        }
        return false;
    }

    public function getImpulseCounterValue(IODeviceChannel $channel): array {
        $value = $this->getRawValue('IC', $channel);
        if ($value !== false) {
            $matched = preg_match('#^VALUE:(\d+),(\d+),(\d+),(\d+),(\d+),([A-Z]*),(.*)$#', $value, $match);
            if ($matched) {
                list(, $totalCost, $pricePerUnit, $impulsesPerUnit, $counter, $calculatedValue, $currency, $unit) = $match;
                return [
                    'totalCost' => $totalCost / 100,
                    'pricePerUnit' => $pricePerUnit / 10000,
                    'impulsesPerUnit' => $impulsesPerUnit,
                    'counter' => $counter,
                    'calculatedValue' => $calculatedValue / 1000,
                    'currency' => $currency ?: null,
                    'unit' => $unit ? trim(base64_decode($unit)) ?: null : null,
                ];
            }
        }
        return [];
    }

    public function getElectricityMeterValue(IODeviceChannel $channel): array {
        $value = $this->getRawValue('EM', $channel);
        if ($value !== false) {
            $numberPlaceholders = str_repeat('(\d+),', 37);
            $matched = preg_match('#^VALUE:' . $numberPlaceholders . '([A-Z]*)$#', $value, $match);
            if ($matched) {
                unset($match[0]);
                $keys = ['support',
                    'frequency', 'voltagePhase1', 'voltagePhase2', 'voltagePhase3', 'currentPhase1', 'currentPhase2', 'currentPhase3',
                    'powerActivePhase1', 'powerActivePhase2', 'powerActivePhase3', 'powerReactivePhase1', 'powerReactivePhase2',
                    'powerReactivePhase3', 'powerApparentPhase1', 'powerApparentPhase2', 'powerApparentPhase3', 'powerFactorPhase1',
                    'powerFactorPhase2', 'powerFactorPhase3', 'phaseAnglePhase1', 'phaseAnglePhase2', 'phaseAnglePhase3',
                    'totalForwardActiveEnergyPhase1', 'totalForwardActiveEnergyPhase2', 'totalForwardActiveEnergyPhase3',
                    'totalReverseActiveEnergyPhase1', 'totalReverseActiveEnergyPhase2', 'totalReverseActiveEnergyPhase3',
                    'totalForwardReactiveEnergyPhase1', 'totalForwardReactiveEnergyPhase2', 'totalForwardReactiveEnergyPhase3',
                    'totalReverseReactiveEnergyPhase1', 'totalReverseReactiveEnergyPhase2', 'totalReverseReactiveEnergyPhase3',
                    'totalCost', 'pricePerUnit', 'currency'];
                $state = array_combine($keys, $match);
//                return ElectricityMeterSupportBits::nullifyUnsupportedFeatures($state['support'], $state);
                return $state;
            }
        }
        return [];
    }

    public function executeSetCommand(string $command) {
        $result = $this->executeCommand($command);
        if (!$result || preg_match("/^OK:/", $result) !== 1) {
            throw new ServiceUnavailableHttpException(30, 'SUPLA Server was unable to execute the action.'); // i18n
        }
    }
}
