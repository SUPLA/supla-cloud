<?php
// SPDX-FileCopyrightText: AC SOFTWARE SP. Z O.O.
// SPDX-License-Identifier: GPL-2.0-or-later

namespace App\Model\Ocpp;

use App\Entity\EntityUtils;
use App\Entity\Main\IODevice;
use App\Entity\Main\IODeviceChannel;
use App\Entity\Main\Location;
use App\Entity\Main\OcppChargingStation;
use App\Entity\Main\User;
use App\Enums\ChannelFlistRelay;
use App\Enums\ChannelFunction;
use App\Enums\ChannelType;
use App\Supla\SuplaServerAware;
use Assert\Assertion;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;

class OcppChargingStationManager {
    use SuplaServerAware;

    public function __construct(private EntityManagerInterface $em, private OcppConfiguration $configuration) {
    }

    public function create(User $user, Location $location, string $name): array {
        Assertion::true($this->configuration->isEnabled(), 'OCPP is not configured on this server.'); // i18n
        Assertion::true($location->belongsToUser($user));
        $name = trim($name);
        Assertion::betweenLength($name, 1, 100, 'Charger name must contain between 1 and 100 characters.'); // i18n
        $key = random_bytes(16);
        $station = $this->em->wrapInTransaction(function () use ($user, $location, $name, $key) {
            // Serialize registrations for this account so concurrent requests cannot bypass its device limit.
            $this->em->lock($user, LockMode::PESSIMISTIC_WRITE);
            Assertion::lessThan(
                $this->em->getRepository(IODevice::class)->count(['user' => $user]),
                $user->getLimits()['ioDevice'],
                'Device limit has been exceeded.' // i18n
            );
            $device = new IODevice();
            foreach (
                [
                'user' => $user, 'location' => $location, 'name' => $name, 'isVirtual' => true,
                'guid' => random_bytes(16), 'regDate' => new \DateTime(), 'protocolVersion' => 26, 'softwareVersion' => '1.0',
                ] as $field => $value
            ) {
                EntityUtils::setField($device, $field, $value);
            }
            $identity = 'U' . $user->getId() . '-' . implode('-', str_split(strtoupper(bin2hex(random_bytes(12))), 4));
            $station = new OcppChargingStation($user, $device, $identity, hash('sha512', $key));
            $this->em->persist($device);
            $this->em->persist($station);
            foreach (
                [
                [ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER, 0, 'Electricity meter'],
                [ChannelType::RELAY, ChannelFunction::POWERSWITCH, ChannelFlistRelay::POWERSWITCH, 'Charging'],
                ] as $number => [$type, $function, $funcList, $caption]
            ) {
                $channel = new IODeviceChannel();
                foreach (
                    [
                    'user' => $user, 'iodevice' => $device, 'channelNumber' => $number, 'isVirtual' => true,
                    'type' => $type, 'function' => $function, 'funcList' => $funcList, 'caption' => $caption,
                    ] as $field => $value
                ) {
                    EntityUtils::setField($channel, $field, $value);
                }
                if ($type === ChannelType::ELECTRICITYMETER) {
                    EntityUtils::setField($channel, 'properties', json_encode([
                        'countersAvailable' => ['forwardActiveEnergy'],
                    ]));
                }
                $device->getChannels()->add($channel);
                $this->em->persist($channel);
            }
            // No ChannelValue/extended value: only real gateway reports initialize readings.
            return $station;
        });
        foreach ($station->getIODevice()->getChannels() as $channel) {
            $this->suplaServer->userAction('ON-CHANNEL-ADDED', [$station->getIODevice()->getId(), $channel->getId()], $user);
        }
        return $this->configuration->describe($station) + ['authorizationKey' => strtoupper(bin2hex($key))];
    }

    public function regenerateKey(OcppChargingStation $station): array {
        $key = random_bytes(16);
        $station->setAuthKeyHash(hash('sha512', $key));
        $this->em->flush();
        return $this->configuration->describe($station) + ['authorizationKey' => strtoupper(bin2hex($key))];
    }
}
