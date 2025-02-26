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
use SuplaBundle\Entity\Main\ClientApp;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\Scene;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\ConnectionStatus;
use SuplaBundle\Exception\ApiExceptionWithDetails;
use SuplaBundle\Exception\SceneDuringExecutionException;
use SuplaBundle\Exception\SceneDuringInactivePeriodException;
use SuplaBundle\Model\ChannelStateGetter\ElectricityMeterChannelState;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Model\LocalSuplaCloud;
use SuplaBundle\Utils\NumberUtils;

abstract class SuplaServer {
    use CurrentUserAware;

    /** @var string */
    protected $socketPath;
    /** @var LocalSuplaCloud */
    protected $localSuplaCloud;
    /** @var LoggerInterface */
    private $logger;
    /** @var array */
    private $commandContext = [];
    /** @var array */
    private $postponedCommands = [];

    public function __construct(string $socketPath, LocalSuplaCloud $localSuplaCloud, LoggerInterface $logger) {
        $this->socketPath = $socketPath;
        $this->localSuplaCloud = $localSuplaCloud;
        $this->logger = $logger;
    }

    public function __destruct() {
        $this->disconnect();
    }

    abstract protected function ensureCanConnect(): void;

    abstract protected function connect();

    abstract protected function disconnect();

    abstract protected function command($command);

    public function getServerStatus(): string {
        try {
            $this->ensureCanConnect();
            $result = $this->doExecuteCommand('GET-STATUS');
            if ($result && trim($result)) {
                return trim($result);
            }
        } catch (SuplaServerIsDownException $e) {
            return $e->getStatusMessage();
        }
        return 'DOWN';
    }

    public function setCommandContext(string $name, string $value): void {
        $this->commandContext[$name] = $value;
    }

    public function clearCommandContext(): void {
        $this->commandContext = [];
    }

    private function doConnectAndExecuteCommand(string $command) {
        if ($this->connect() !== false) {
            if ($this->commandContext) {
                $commandSuffix = implode(',', array_map(function ($key, $value) {
                    return $key . '=' . base64_encode($value);
                }, array_keys($this->commandContext), $this->commandContext));
                $command = $command . ',' . $commandSuffix;
            }
            $result = $this->command($command);
        } else {
            throw new SuplaServerIsDownException();
        }
        $this->logger->debug('SuplaServer command', ['command' => $command, 'result' => $result]);
        return $result;
    }

    private function doExecuteCommand(string $command) {
        try {
            return $this->doConnectAndExecuteCommand($command);
        } catch (SuplaSocketWriteException $e) {
            $this->logger->warning('Failed attempt to execute the command. Retrying...');
            $this->disconnect();
        }

        try {
            return $this->doConnectAndExecuteCommand($command);
        } catch (SuplaSocketWriteException $e) {
            $this->logger->error('Retrying the command failed. Unable to write socket.');
            $this->disconnect();
        }
    }

    private function getConnectionStatus(string $what, int ...$args): ConnectionStatus {
        $args = implode(',', $args);
        try {
            $result = $this->doExecuteCommand("IS-$what-CONNECTED:$args");
            if (is_string($result)) {
                if (str_starts_with($result, 'CONNECTED:')) {
                    return ConnectionStatus::CONNECTED();
                } elseif (str_starts_with($result, 'CONNECTED_BUT_NOT_AVAILABLE:')) {
                    return ConnectionStatus::CONNECTED_NOT_AVAILABLE();
                } elseif (str_starts_with($result, 'OFFLINE_REMOTE_WAKEUP_NOT_SUPPORTED:')) {
                    return ConnectionStatus::OFFLINE_REMOTE_WAKEUP_NOT_SUPPORTED();
                } elseif (str_starts_with($result, 'FIRMWARE_UPDATE_ONGOING:')) {
                    return ConnectionStatus::FIRMWARE_UPDATE_ONGOING();
                }
            }
        } catch (SuplaServerIsDownException $e) {
            $this->logger->error('SUPLA Server is down.');
        }
        return ConnectionStatus::NOT_CONNECTED();
    }

    public function isClientAppConnected(ClientApp $clientApp): bool {
        if (!$clientApp->getEnabled()) {
            return false;
        }
        return $this->getConnectionStatus('CLIENT', $clientApp->getUser()->getId(), $clientApp->getId())->isConnected();
    }

    public function isDeviceConnected(IODevice $device) {
        if (!$device->getEnabled()) {
            return false;
        }
        return $this->getConnectionStatus('IODEV', $device->getUser()->getId(), $device->getId())->isConnected();
    }

    public function isChannelConnected(IODeviceChannel $channel) {
        return $this->getChannelConnectionStatus($channel)->isConnected();
    }

    public function getChannelConnectionStatus(IODeviceChannel $channel): ConnectionStatus {
        if (!$channel->getIoDevice()->getEnabled()) {
            return ConnectionStatus::NOT_CONNECTED();
        }
        return $this->getConnectionStatus('CHANNEL', $channel->getUser()->getId(), $channel->getIoDevice()->getId(), $channel->getId());
    }

    public function userAction($action, $params = [], User $user = null): bool {
        $userId = $user ? $user->getId() : $this->getCurrentUserOrThrow()->getId();
        $command = "USER-{$action}:{$userId}";
        if ($params) {
            $params = is_array($params) ? $params : [$params];
            $command .= ',' . implode(',', $params);
        }
        $result = $this->doExecuteCommand($command);
        return $result !== false && preg_match("/^OK:" . $userId . "\n/", $result) === 1;
    }

    public function postponeUserAction($action, $params = [], User $user = null): void {
        $userId = $user ? $user->getId() : $this->getCurrentUserOrThrow()->getId();
        $command = "USER-{$action}:{$userId}";
        if ($params) {
            $params = is_array($params) ? $params : [$params];
            $command .= ',' . implode(',', $params);
        }
        $this->postponeCommand($command);
    }

    public function postponeCommand(string $command, array $params = []): void {
        $commandString = $command . ($params ? ':' . implode(',', $params) : '');
        $postponedCommand = ['command' => $commandString];
        if (!in_array($postponedCommand, $this->postponedCommands)) {
            $this->postponedCommands[] = $postponedCommand;
        }
    }

    public function deviceAction(IODevice $device, string $commandName, array $params = []): bool {
        $params = implode(',', array_merge([$device->getUser()->getId(), $device->getId()], $params));
        $command = "$commandName:$params";
        $result = $this->doExecuteCommand($command);
        return $result !== false && str_starts_with($result, 'OK:');
    }

    public function channelAction(IODeviceChannel $channel, string $commandName): bool {
        $params = implode(',', [$channel->getUser()->getId(), $channel->getIoDevice()->getId(), $channel->getId()]);
        $command = "$commandName:$params";
        $result = $this->doExecuteCommand($command);
        return $result !== false && str_starts_with($result, 'OK:');
    }

    public function reconnect(User $user = null): void {
        $userId = $user ? $user->getId() : $this->getCurrentUserOrThrow()->getId();
        $this->postponeCommand('USER-RECONNECT', [$userId]);
    }

    public function amazonAlexaCredentialsChanged(): bool {
        return $this->userAction('ALEXA-CREDENTIALS-CHANGED');
    }

    public function stateWebhookChanged(): bool {
        return $this->userAction('ON-STATE-WEBHOOK-CHANGED');
    }

    public function mqttSettingsChanged(): bool {
        return $this->userAction('ON-MQTT-SETTINGS-CHANGED');
    }

    public function googleHomeCredentialsChanged(): bool {
        return $this->userAction('GOOGLE-HOME-CREDENTIALS-CHANGED');
    }

    public function getPushNotificationLimit(User $user = null): array {
        $userId = $user ? $user->getId() : $this->getCurrentUserOrThrow()->getId();
        $command = "PN-GET-LIMIT:$userId";
        $result = $this->doExecuteCommand($command);
        if ($result && preg_match('#PN-LIMIT:(-?\d+),(-?\d+)#', $result, $match)) {
            [, $limit, $left] = $match;
            return ['limit' => intval($limit), 'left' => intval($left)];
        } else {
            throw new ApiExceptionWithDetails(
                'SUPLA Server was unable to check the push notification limits.',
                ['error' => 'suplaServerError', 'response' => $result],
            );
        }
    }

    public function onOAuthClientRemoved(): bool {
        return $this->amazonAlexaCredentialsChanged();
    }

    public function clientReconnect(ClientApp $clientApp) {
        $result = $this->doExecuteCommand("CLIENT-RECONNECT:" . $clientApp->getUser()->getId() . "," . $clientApp->getId());
        return $result !== false && preg_match("/^OK:" . $clientApp->getId() . "\n/", $result) === 1 ? true : false;
    }

    public function getRawValue($type, IODeviceChannel $channel) {
        $args = [$channel->getUser()->getId(), $channel->getIoDevice()->getId(), $channel->getId()];
        $result = $this->doExecuteCommand("GET-" . $type . "-VALUE:" . implode(',', $args));
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

    public function executeScene(Scene $scene, string $command = 'EXECUTE-SCENE'): void {
        $command = $scene->buildServerActionCommand($command);
        $result = $this->doExecuteCommand($command) ?: '';
        if (str_starts_with($result, 'IS-DURING-EXECUTION:')) {
            throw new SceneDuringExecutionException($scene);
        } elseif (str_starts_with($result, 'INACTIVE-PERIOD:')) {
            throw new SceneDuringInactivePeriodException($scene);
        } elseif (!str_starts_with($result, 'OK:')) {
            throw new ApiExceptionWithDetails(
                'SUPLA Server was unable to execute the scene.', // i18n
                ['error' => 'suplaServerError', 'response' => $result],
            );
        }
    }

    /**
     * @param Scene $scene
     * @return array [$initiatorType, $initiatorId, $initiatorNameBase64, $msFromStart, $msToEnd]
     */
    public function getSceneSummary(Scene $scene): array {
        $command = sprintf('GET-SCENE-SUMMARY:%d,%d', $scene->getUser()->getId(), $scene->getId());
        $result = $this->doExecuteCommand($command);
        $prefix = sprintf('SUMMARY:%d,', $scene->getId());
        if (strpos($result, $prefix) === 0) {
            return explode(',', substr($result, strlen($prefix)));
        } else {
            throw new ApiExceptionWithDetails(
                'SUPLA Server was unable to query for the scene state.', // i18n
                ['error' => 'suplaServerError', 'response' => $result],
            );
        }
    }

    public function executeCommand(string $command): string {
        $result = $this->doExecuteCommand($command);
        if (!$result || preg_match("/^[A-Z-]+:/", $result) !== 1) {
            throw new ApiExceptionWithDetails(
                'SUPLA Server was unable to execute the action.', // i18n
                ['error' => 'suplaServerError', 'response' => $result],
            );
        }
        return $result;
    }

    public function flushPostponedCommands(): array {
        $results = [];
        foreach ($this->postponedCommands as $postponedCommand) {
            $results[] = $this->doExecuteCommand($postponedCommand['command']);
        }
        $this->postponedCommands = [];
        return $results;
    }

    public function channelConfigChanged(IODeviceChannel $channel, int $changeBits): void {
        if ($changeBits) {
            $params = [$channel->getType()->getId(), $channel->getFunction()->getId(), $changeBits];
            $command = $channel->buildServerActionCommand('USER-ON-CHANNEL-CONFIG-CHANGED', $params);
            $this->postponeCommand($command);
        }
    }
}
