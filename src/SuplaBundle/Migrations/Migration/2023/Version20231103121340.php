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

namespace SuplaBundle\Migrations\Migration;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Migrations\Factory\ChannelDependenciesAware;
use SuplaBundle\Migrations\Factory\EntityManagerAware;
use SuplaBundle\Migrations\NoWayBackMigration;
use SuplaBundle\Model\Dependencies\ChannelDependencies;

/**
 * Ensure the related channels are in the same location.
 */
class Version20231103121340 extends NoWayBackMigration implements EntityManagerAware, ChannelDependenciesAware, LoggerAwareInterface {
    /** @var ChannelDependencies */
    private $channelDependencies;
    /** @var EntityManagerInterface */
    private $em;
    /** @var LoggerInterface */
    private $migrationsLogger;

    private const PARENT_FUNCTIONS = [
        ChannelFunction::CONTROLLINGTHEGATEWAYLOCK,
        ChannelFunction::CONTROLLINGTHEGATE,
        ChannelFunction::CONTROLLINGTHEGARAGEDOOR,
        ChannelFunction::CONTROLLINGTHEDOORLOCK,
        ChannelFunction::CONTROLLINGTHEROLLERSHUTTER,
        ChannelFunction::CONTROLLINGTHEROOFWINDOW,
        ChannelFunction::POWERSWITCH,
        ChannelFunction::LIGHTSWITCH,
        ChannelFunction::STAIRCASETIMER,
        ChannelFunction::HVAC_THERMOSTAT,
        ChannelFunction::HVAC_THERMOSTAT_AUTO,
        ChannelFunction::HVAC_THERMOSTAT_DIFFERENTIAL,
        ChannelFunction::HVAC_DOMESTIC_HOT_WATER,
    ];

    /** @required */
    public function setChannelDependencies(ChannelDependencies $channelDependencies): void {
        $this->channelDependencies = $channelDependencies;
    }

    public function setEntityManager(EntityManagerInterface $em): void {
        $this->em = $em;
    }

    public function setLogger(LoggerInterface $logger) {
        $this->migrationsLogger = $logger;
    }

    public function migrate() {
        $parentFunctions = implode(',', self::PARENT_FUNCTIONS);
        $channels = $this->fetchAll("SELECT id FROM supla_dev_channel WHERE func IN($parentFunctions)");
        $changeLocationOperations = [];
        foreach ($channels as $channelData) {
            $channel = $this->em->find(IODeviceChannel::class, $channelData['id']);
            $dependencies = $this->channelDependencies->getItemsThatDependOnLocation($channel);
            if ($dependencies['channels']) {
                $expectedLocationId = $channel->getLocation()->getId();
                foreach ($dependencies['channels'] as $depChannel) {
                    /** @var IODeviceChannel $depChannel */
                    if ($depChannel->getLocation()->getId() !== $expectedLocationId) {
                        $changeLocationOperations[$depChannel->getId()] = $this->changeLocationOperation($depChannel, $expectedLocationId);
                    }
                }
            }
        }
        // make sure that everything is migrated
        $channels = $this->fetchAll("SELECT id FROM supla_dev_channel WHERE func NOT IN($parentFunctions)");
        foreach ($channels as $channelData) {
            $channel = $this->em->find(IODeviceChannel::class, $channelData['id']);
            $dependencies = $this->channelDependencies->getItemsThatDependOnLocation($channel);
            if ($dependencies['channels']) {
                $expectedLocationId = $channel->getLocation()->getId();
                if (isset($changeLocationOperations[$channel->getId()])) {
                    $expectedLocationId = $changeLocationOperations[$channel->getId()]['newId'];
                }
                foreach ($dependencies['channels'] as $depChannel) {
                    /** @var IODeviceChannel $depChannel */
                    if (in_array($depChannel->getFunction()->getId(), self::PARENT_FUNCTIONS)) {
                        continue;
                    }
                    if ($depChannel->getLocation()->getId() !== $expectedLocationId) {
                        $changeLocationOperation = $this->changeLocationOperation($depChannel, $expectedLocationId);
                        $changeLocationOperation['WARNING'] = 'No parent function chosen for this relation.';
                        $changeLocationOperations[$depChannel->getId()] = $changeLocationOperation;
                    }
                }
            }
        }
        foreach ($changeLocationOperations as $channelId => $changeOperation) {
            $log = sprintf(
                'Moved channel ID=%d from location ID=%d to ID=%d.',
                $channelId,
                $changeOperation['oldId'],
                $changeOperation['newId']
            );
            $this->log($log, $changeOperation);
            $this->addSql('UPDATE supla_dev_channel SET location_id=:locationId WHERE id=:id', [
                'id' => $channelId,
                'locationId' => $changeOperation['newId'],
            ]);
        }
    }

    private function changeLocationOperation(IODeviceChannel $channel, int $newLocationId): array {
        return [
            'channelId' => $channel->getId(),
            'oldId' => $channel->getLocation()->getId(),
            'newId' => $newLocationId,
            'functionId' => $channel->getFunction()->getId(),
            'functionName' => $channel->getFunction()->getName(),
            'revertSql' => "UPDATE supla_dev_channel SET location_id={$channel->getLocation()->getId()} WHERE id={$channel->getId()};",
        ];
    }

    private function log(string $message, array $details = []): void {
        $this->migrationsLogger->debug($message, array_merge([
            'migrationClass' => get_class($this),
        ], $details));
    }
}
