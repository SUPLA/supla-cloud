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
use SuplaBundle\Entity\User;
use SuplaBundle\Model\ChannelStateGetter\ElectricityMeterChannelState;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Model\LocalSuplaCloud;
use SuplaBundle\Utils\NumberUtils;
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
            throw new SuplaServerIsDownException();
        }
        $this->logger->debug('SuplaServer command', ['command' => $command, 'result' => $result]);
        return $result;
    }

    private function isConnected(string $what, int ...$args): bool {
        $args = implode(',', $args);
        try {
            $result = $this->executeCommand("IS-$what-CONNECTED:$args");
            return $result !== false && preg_match("/^CONNECTED:\d+\n/", $result) === 1 ? true : false;
        } catch (SuplaServerIsDownException $e) {
            $this->logger->error('SUPLA Server is down.');
            return false;
        }
    }

    public function isClientAppConnected(ClientApp $clientApp): bool {
        if (!$clientApp->getEnabled()) {
            return false;
        }
        return $this->isConnected('CLIENT', $clientApp->getUser()->getId(), $clientApp->getId());
    }

    public function isDeviceConnected(IODevice $device) {
        if (!$device->getEnabled()) {
            return false;
        }
        return $this->isConnected('IODEV', $device->getUser()->getId(), $device->getId());
    }

    public function isChannelConnected(IODeviceChannel $channel) {
        if (!$channel->getIoDevice()->getEnabled()) {
            return false;
        }
        return $this->isConnected('CHANNEL', $channel->getUser()->getId(), $channel->getIoDevice()->getId(), $channel->getId());
    }

    public function userAction($action, $params = [], User $user = null): bool {
        $userId = $user ? $user->getId() : $this->getCurrentUserOrThrow()->getId();
        $command = "USER-{$action}:{$userId}";
        if ($params) {
            $params = is_array($params) ? $params : [$params];
            $command .= ',' . implode(',', $params);
        }
        $result = $this->executeCommand($command);
        return $result !== false && preg_match("/^OK:" . $userId . "\n/", $result) === 1;
    }

    public function channelAction(IODeviceChannel $channel, string $commandName): bool {
        $params = implode(',', [$channel->getUser()->getId(), $channel->getIoDevice()->getId(), $channel->getId()]);
        $command = "$commandName:$params";
        $result = $this->executeCommand($command);
        return $result !== false && preg_match("/^OK:" . $channel->getId() . "\n/", $result) === 1;
    }

    public function reconnect(User $user = null): bool {
        return $this->userAction('RECONNECT', [], $user);
    }

    public function amazonAlexaCredentialsChanged(): bool {
        return $this->userAction('ALEXA-CREDENTIALS-CHANGED');
    }

    public function onDeviceSettingsChanged(IODevice $device): bool {
        return $this->userAction('ON-DEVICE-SETTINGS-CHANGED', [$device->getId()]);
    }

    public function stateWebhookChanged(): bool {
        return $this->userAction('STATE-WEBHOOK-CHANGED');
    }

    public function mqttSettingsChanged(): bool {
        return $this->userAction('MQTT-SETTINGS-CHANGED');
    }

    public function googleHomeCredentialsChanged(): bool {
        return $this->userAction('GOOGLE-HOME-CREDENTIALS-CHANGED');
    }

    public function onOAuthClientRemoved(): bool {
        return $this->amazonAlexaCredentialsChanged();
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

    public function getValue(string $type, IODeviceChannel $channel) {
        $result = $this->getRawValue($type, $channel);
        if ($result !== false) {
            [$val] = sscanf($result, "VALUE:%f\n");

            if (is_numeric($val)) {
                return $val;
            }
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

    public function getDoubleValue(IODeviceChannel $channel) {
        return $this->getValue('DOUBLE', $channel);
    }

    public function getRgbwValue(IODeviceChannel $channel) {
        $value = $this->getRawValue('RGBW', $channel);
        if ($value !== false) {
            [$color, $color_brightness, $brightness] = sscanf($value, "VALUE:%i,%i,%i\n");
            if (is_numeric($color) && is_numeric($color_brightness) && is_numeric($brightness)) {
                return ['color' => $color, 'color_brightness' => $color_brightness, 'brightness' => $brightness];
            }
        }
        return false;
    }

    public function getValveValue(IODeviceChannel $channel): array {
        $result = $this->getRawValue('VALVE', $channel);
        if ($result !== false) {
            return sscanf($result, "VALUE:%d,%d\n");
        }
        return [null, null];
    }

    public function getRelayValue(IODeviceChannel $channel): array {
        $result = $this->getRawValue('RELAY', $channel);
        if ($result !== false) {
            return sscanf($result, "VALUE:%d,%d\n");
        }
        return [null, null];
    }

    public function getImpulseCounterValue(IODeviceChannel $channel): array {
        $value = $this->getRawValue('IC', $channel);
        if ($value !== false) {
            $numberPlaceholders = str_repeat('(-?\d+),', 5);
            $matched = preg_match('#^VALUE:' . $numberPlaceholders . '([A-Z]*),(.*)$#', $value, $match);
            if ($matched) {
                [, $totalCost, $pricePerUnit, $impulsesPerUnit, $counter, $calculatedValue, $currency, $unit] = $match;
                return [
                    'totalCost' => NumberUtils::maximumDecimalPrecision($totalCost * 0.01, 2),
                    'pricePerUnit' => NumberUtils::maximumDecimalPrecision($pricePerUnit * 0.0001, 4),
                    'impulsesPerUnit' => intval($impulsesPerUnit),
                    'counter' => intval($counter),
                    'calculatedValue' => NumberUtils::maximumDecimalPrecision($calculatedValue * 0.001, 3),
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
            $numberPlaceholders = str_repeat('(-?\d+),', 37);
            $matched = preg_match('#^VALUE:' . $numberPlaceholders . '([A-Z]*)$#', $value, $match);
            if ($matched) {
                unset($match[0]);
                return (new ElectricityMeterChannelState($match))->toArray();
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
