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

namespace SuplaBundle\Command\Cyclic;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Exception\ApiException;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Repository\IODeviceChannelRepository;
use SuplaBundle\Supla\SuplaOcrClient;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SynchronizeOcrAuthKeysCommand extends AbstractCyclicCommand {
    use Transactional;

    private SuplaOcrClient $ocrClient;
    private IODeviceChannelRepository $channelRepository;

    public function __construct(SuplaOcrClient $ocrClient, IODeviceChannelRepository $channelRepository) {
        parent::__construct();
        $this->ocrClient = $ocrClient;
        $this->channelRepository = $channelRepository;
    }

    protected function configure() {
        $this
            ->setHidden(true)
            ->setName('supla:cyclic:synchronize-ocr-authkeys')
            ->setDescription('Sends OCR AuthKeys from supported devices that were not synced yet.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->transactional(function (EntityManagerInterface $em) use ($input, $output) {
            $io = new SymfonyStyle($input, $output);
            $query = $this->channelRepository->createQueryBuilder('c')
                ->where('c.type = :type')
                ->andWhere("c.properties LIKE '%\"ocr\"%'")
                ->andWhere("c.properties NOT LIKE '%\"ocrSynced\"%'")
                ->setParameter('type', ChannelType::IMPULSECOUNTER)
                ->getQuery();
            if ($input->isInteractive()) {
                $io->title("Synchronize OCR AuthKeys");
                $paginator = new Paginator($query);
                $count = $paginator->count();
                if ($count) {
                    $io->writeln("Number of channels to synchronize: " . $count);
                    $io->progressStart($count);
                } else {
                    $io->writeln("No channels to synchronize.");
                    return;
                }
            }
            foreach ($query->toIterable() as $icChannel) {
                /** @var IODeviceChannel $icChannel */
                $props = $icChannel->getProperties();
                try {
                    $this->ocrClient->registerDevice($icChannel);
                    $props['ocr']['ocrSynced'] = true;
                    EntityUtils::setField($icChannel, 'properties', json_encode($props));
                    $em->persist($icChannel);
                } catch (ApiException $e) {
                    $io->error($e->getMessage());
                }
                if ($input->isInteractive()) {
                    $io->progressAdvance();
                }
            }
            if ($input->isInteractive()) {
                $io->progressFinish();
            }
        });
        return 0;
    }

    public function getIntervalInMinutes(): int {
        return 10; // every ten minutes
    }
}
