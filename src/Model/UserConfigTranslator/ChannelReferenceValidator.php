<?php

namespace App\Model\UserConfigTranslator;

use App\Entity\Main\IODeviceChannel;
use App\Enums\ChannelFunction as CF;
use App\Enums\ChannelType;
use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;

/** Business references: identity, ownership and capability; never reachability. */
class ChannelReferenceValidator {
    public const ROLES = [
        'mainThermometerChannelId', 'auxThermometerChannelId', 'binarySensorChannelId',
        'masterThermostatChannelId', 'pumpSwitchChannelId', 'heatOrColdSourceSwitchChannelId',
        'levelSensorChannelIds', 'floodSensorChannelIds',
    ];

    public function __construct(private EntityManagerInterface $entityManager) {
    }

    public function resolve(IODeviceChannel $destination, int $id, string $role): IODeviceChannel {
        $channel = $this->entityManager->find(IODeviceChannel::class, $id);
        Assertion::isObject($channel, 'Invalid channel ID given: ' . $id);
        Assertion::eq($destination->getUser()->getId(), $channel->getUser()->getId(), 'Invalid channel owner.');
        Assertion::notEq($destination->getId(), $channel->getId(), 'A channel cannot reference itself.');
        if ($destination->getIoDevice()->getId() !== $channel->getIoDevice()->getId()) {
            Assertion::true($destination->getIoDevice()->getFlags()['suplanSupported'], 'Destination does not support SupLAN.');
            Assertion::true($channel->getIoDevice()->getFlags()['suplanSupported'], 'Source does not support SupLAN.');
        }
        Assertion::true($this->compatible($destination, $channel, $role), 'Incompatible channel type or function.', $role);
        return $channel;
    }

    /** Also used by the normal function-change cleanup, with the prospective function. */
    public function compatible(IODeviceChannel $destination, IODeviceChannel $source, string $role, ?CF $function = null): bool {
        $type = $source->getType()->getId();
        $functionId = ($function ?? $source->getFunction())->getId();
        switch ($role) {
            case 'mainThermometerChannelId':
            case 'auxThermometerChannelId':
                return in_array($type, [ChannelType::THERMOMETER, ChannelType::HUMIDITYANDTEMPSENSOR])
                    && in_array($functionId, [CF::THERMOMETER, CF::HUMIDITYANDTEMPERATURE]);
            case 'masterThermostatChannelId':
                return $type === ChannelType::HVAC && in_array($functionId, [
                    CF::HVAC_THERMOSTAT, CF::HVAC_THERMOSTAT_HEAT_COOL, CF::HVAC_THERMOSTAT_DIFFERENTIAL, CF::HVAC_DOMESTIC_HOT_WATER,
                ]);
            case 'pumpSwitchChannelId':
            case 'heatOrColdSourceSwitchChannelId':
                return $type === ChannelType::RELAY
                    && $functionId === ($role === 'pumpSwitchChannelId' ? CF::PUMPSWITCH : CF::HEATORCOLDSOURCESWITCH);
            case 'binarySensorChannelId':
            case 'levelSensorChannelIds':
            case 'floodSensorChannelIds':
                // Any active binary-sensor function is accepted; no flood/container-only restriction.
                $functions = array_map(fn(CF $fn) => $fn->getId(), ChannelType::functions()[ChannelType::SENSORNO]);
                return $type === ChannelType::SENSORNO && in_array($functionId, $functions);
            default:
                return false;
        }
    }
}
