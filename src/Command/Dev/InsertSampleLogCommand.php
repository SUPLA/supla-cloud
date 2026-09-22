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

namespace App\Command\Dev;

use App\Entity\EntityUtils;
use App\Entity\Main\IODeviceChannel;
use App\Entity\MeasurementLogs\ElectricityMeterLogItem;
use App\Enums\ChannelType;
use App\Model\MeasurementLogsEntityManagerProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InsertSampleLogCommand extends Command {
    private const ELECTRICITY_METER_FIELDS = [
        'phase1_fae',
        'phase1_rae',
        'phase1_fre',
        'phase1_rre',
        'phase2_fae',
        'phase2_rae',
        'phase2_fre',
        'phase2_rre',
        'phase3_fae',
        'phase3_rae',
        'phase3_fre',
        'phase3_rre',
        'fae_balanced',
        'rae_balanced',
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MeasurementLogsEntityManagerProvider $measurementLogsEntityManagerProvider,
    ) {
        parent::__construct();
        $this->ignoreValidationErrors();
    }

    protected function configure() {
        $this
            ->setName('supla:dev:insertSampleLog')
            ->setDescription('Inserts a sample measurement log for a channel.')
            ->addArgument('channelId', InputArgument::REQUIRED, 'Channel ID.')
            ->addArgument('time', InputArgument::IS_ARRAY, 'Log time, e.g. "+5 minutes" or "-15 minutes". Defaults to current UTC time.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        try {
            [$channelId, $timeTokens] = $this->resolveCliArguments($input);
            $channel = $this->loadChannel($channelId);
            $logDate = $this->resolveLogDate($timeTokens);
            $this->ensureNoLogExistsAt($channel, $logDate);
            $log = $this->buildSampleLog($channel, $logDate);
            $logsEntityManager = $this->measurementLogsEntityManagerProvider->get();
            $logsEntityManager->persist($log);
            $logsEntityManager->flush();

            $output->writeln(sprintf(
                '<info>Inserted sample %s log for channel #%d at %s UTC.</info>',
                strtolower($channel->getType()->getKey()),
                $channel->getId(),
                $logDate->format('Y-m-d H:i:s')
            ));
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            return Command::FAILURE;
        }
    }

    private function resolveCliArguments(InputInterface $input): array {
        $rawArgs = $_SERVER['argv'] ?? [];
        $commandIndex = array_search($this->getName(), $rawArgs, true);
        if ($commandIndex !== false) {
            $commandArgs = array_slice($rawArgs, $commandIndex + 1);
            if ($commandArgs) {
                $channelId = (int)array_shift($commandArgs);
                return [$channelId, $commandArgs];
            }
        }

        $channelId = (int)$input->getArgument('channelId');
        $timeTokens = $input->getArgument('time');
        if (!is_array($timeTokens)) {
            $timeTokens = $timeTokens ? [$timeTokens] : [];
        }

        return [$channelId, $timeTokens];
    }

    private function loadChannel(int $channelId): IODeviceChannel {
        $channel = $this->entityManager->find(IODeviceChannel::class, $channelId);
        if (!$channel) {
            throw new \InvalidArgumentException(sprintf('Channel #%d does not exist.', $channelId));
        }
        if ($channel->getType()->getId() !== ChannelType::ELECTRICITYMETER) {
            throw new \InvalidArgumentException(sprintf(
                'Channel #%d has unsupported type %s. Only ELECTRICITYMETER is supported.',
                $channelId,
                $channel->getType()->getKey()
            ));
        }
        return $channel;
    }

    private function resolveLogDate(array $timeArgument): \DateTimeImmutable {
        $timeArgument = array_values(array_filter($timeArgument, fn(string $token) => $token !== '--'));
        $timeExpression = trim(implode(' ', $timeArgument));
        try {
            $logDate = $timeExpression
                ? new \DateTimeImmutable($timeExpression, new \DateTimeZone('UTC'))
                : new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        } catch (\Exception) {
            throw new \InvalidArgumentException(sprintf('Cannot parse log time "%s".', $timeExpression));
        }
        return $logDate->setTimezone(new \DateTimeZone('UTC'));
    }

    private function ensureNoLogExistsAt(IODeviceChannel $channel, \DateTimeImmutable $logDate): void {
        $existingLog = $this->measurementLogsEntityManagerProvider->get()->getRepository(ElectricityMeterLogItem::class)->findOneBy([
            'channel_id' => $channel->getId(),
            'date' => $logDate->format('Y-m-d H:i:s'),
        ]);
        if ($existingLog) {
            throw new \InvalidArgumentException(sprintf(
                'A log for channel #%d at %s UTC already exists.',
                $channel->getId(),
                $logDate->format('Y-m-d H:i:s')
            ));
        }
    }

    private function buildSampleLog(IODeviceChannel $channel, \DateTimeImmutable $logDate): ElectricityMeterLogItem {
        return match ($channel->getType()->getId()) {
            ChannelType::ELECTRICITYMETER => $this->buildElectricityMeterLog($channel, $logDate),
            default => throw new \InvalidArgumentException(sprintf(
                'No sample log generator for channel type %s.',
                $channel->getType()->getKey()
            )),
        };
    }

    private function buildElectricityMeterLog(IODeviceChannel $channel, \DateTimeImmutable $logDate): ElectricityMeterLogItem {
        $log = new ElectricityMeterLogItem();
        EntityUtils::setField($log, 'channel_id', $channel->getId());
        EntityUtils::setField($log, 'date', $logDate->format('Y-m-d H:i:s'));

        $previousLog = $this->measurementLogsEntityManagerProvider->get()->createQueryBuilder()
            ->select('l')
            ->from(ElectricityMeterLogItem::class, 'l')
            ->where('l.channel_id = :channelId')
            ->andWhere('l.date < :date')
            ->setParameter('channelId', $channel->getId())
            ->setParameter('date', $logDate->format('Y-m-d H:i:s'))
            ->orderBy('l.date', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        foreach (self::ELECTRICITY_METER_FIELDS as $field) {
            $previousValue = $previousLog ? EntityUtils::getField($previousLog, $field) : null;
            if ($previousLog && $previousValue === null) {
                EntityUtils::setField($log, $field, null);
                continue;
            }

            $increment = random_int(1, 5000);
            $baseValue = $previousValue ?? random_int(1, 50000);
            EntityUtils::setField($log, $field, $baseValue + $increment);
        }

        return $log;
    }
}
