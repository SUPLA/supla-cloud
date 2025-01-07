<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Repository\IODeviceChannelRepository;

class RelatedMeterUserConfigTranslator extends UserConfigTranslator {
    private const RELAY_FUNCTIONS = [
        ChannelFunction::POWERSWITCH,
        ChannelFunction::LIGHTSWITCH,
        ChannelFunction::STAIRCASETIMER,
    ];

    private const METER_FUNCTIONS = [
        ChannelFunction::ELECTRICITYMETER,
        ChannelFunction::IC_ELECTRICITYMETER,
        ChannelFunction::IC_GASMETER,
        ChannelFunction::IC_WATERMETER,
        ChannelFunction::IC_HEATMETER,
    ];

    private IODeviceChannelRepository $channelRepository;
    private EntityManagerInterface $em;

    public function __construct(IODeviceChannelRepository $channelRepository, EntityManagerInterface $em) {
        $this->channelRepository = $channelRepository;
        $this->em = $em;
    }

    public function getConfig(HasUserConfig $subject): array {
        if (in_array($subject->getFunction()->getId(), self::RELAY_FUNCTIONS)) {
            return [
                'relatedMeterChannelId' => $subject->getUserConfigValue('relatedMeterChannelId'),
            ];
        } else {
            return [
                'relatedRelayChannelId' => $this->findRelayIdThatPointsToThisMeter($subject),
            ];
        }
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (in_array($subject->getFunction()->getId(), self::RELAY_FUNCTIONS)) {
            $this->setConfigForRelay($subject, $config);
        } else {
            $this->setConfigForMeter($subject, $config);
        }
    }

    public function setConfigForRelay(HasUserConfig $subject, array $config): void {
        if (array_key_exists('relatedMeterChannelId', $config)) {
            if ($config['relatedMeterChannelId']) {
                Assertion::integer($config['relatedMeterChannelId']);
                /** @var IODeviceChannel $newMeterChannel */
                $newMeterChannel = $this->channelRepository->findForUser($subject->getUser(), $config['relatedMeterChannelId']);
                Assertion::inArray($newMeterChannel->getFunction()->getId(), self::METER_FUNCTIONS, 'Invalid meter function.');
                Assertion::eq(
                    $subject->getLocation()->getId(),
                    $newMeterChannel->getLocation()->getId(),
                    'Channels that are meant to work with each other must be in the same location.' // i18n
                );
                Assertion::eq(
                    $subject->getHidden(),
                    $newMeterChannel->getHidden(),
                    'Channels that are meant to work with each other must have the same visibility setting.' // i18n
                );
                $this->setConfigForMeter($newMeterChannel, ['relatedRelayChannelId' => null]);
                $subject->setUserConfigValue('relatedMeterChannelId', $newMeterChannel->getId());
            } else {
                $subject->setUserConfigValue('relatedMeterChannelId', null);
            }
        }
    }

    private function setConfigForMeter(HasUserConfig $subject, array $config) {
        if (array_key_exists('relatedRelayChannelId', $config)) {
            $currentId = $this->findRelayIdThatPointsToThisMeter($subject);
            if ($currentId !== $config['relatedRelayChannelId']) {
                if ($currentId) {
                    $currentRelay = $this->channelRepository->findForUser($subject->getUser(), $currentId);
                    $this->setConfigForRelay($currentRelay, ['relatedMeterChannelId' => null]);
                    $this->em->persist($currentRelay);
                }
                if ($config['relatedRelayChannelId']) {
                    Assertion::integer($config['relatedRelayChannelId']);
                    /** @var IODeviceChannel $newRelayChannel */
                    $newRelayChannel = $this->channelRepository->findForUser($subject->getUser(), $config['relatedRelayChannelId']);
                    Assertion::inArray($newRelayChannel->getFunction()->getId(), self::RELAY_FUNCTIONS, 'Invalid relay function.');
                    $this->setConfigForRelay($newRelayChannel, ['relatedMeterChannelId' => $subject->getId()]);
                    $this->em->persist($newRelayChannel);
                }
            }
        }
    }

    private function findRelayIdThatPointsToThisMeter(HasUserConfig $meter): ?int {
        $relays = $this->channelRepository->createQueryBuilder('c')
            ->where('c.user = :user')
            ->andWhere('c.function IN(:funcs)')
            ->setParameter('user', $meter->getUser())
            ->setParameter('funcs', self::RELAY_FUNCTIONS)
            ->getQuery()
            ->getResult();
        foreach ($relays as $relay) {
            /** @var IODeviceChannel $relay */
            if ($this->getConfig($relay)['relatedMeterChannelId'] === $meter->getId()) {
                return $relay->getId();
            }
        }
        return null;
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), array_merge(self::RELAY_FUNCTIONS, self::METER_FUNCTIONS));
    }
}
