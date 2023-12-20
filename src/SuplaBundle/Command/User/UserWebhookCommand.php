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

namespace SuplaBundle\Command\User;

use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Model\UserManager;
use SuplaBundle\Repository\StateWebhookRepository;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UserWebhookCommand extends Command {
    use SuplaServerAware;
    use CurrentUserAware;

    /** @var UserManager */
    private $userManager;
    /** @var StateWebhookRepository */
    private $stateWebhookRepository;
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        UserManager $userManager,
        StateWebhookRepository $stateWebhookRepository,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->userManager = $userManager;
        $this->stateWebhookRepository = $stateWebhookRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure() {
        $this
            ->setName('supla:user:webhook')
            ->addArgument('username', InputArgument::OPTIONAL)
            ->setDescription('Manages a custom user webhook.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');
        if (!$username) {
            $username = $io->ask('E-mail address: ');
        }
        $user = $this->userManager->userByEmail($username);
        Assertion::notNull($user, 'User does not exists.');
        $this->tokenStorage->setToken(new UsernamePasswordToken($user, null, 'main'));
        $webhook = $this->stateWebhookRepository->findOrCreateForApiClientAndUser(null, $user);
        if ($webhook->getId()) {
            $io->writeln('Current webhook URL: ' . $webhook->getUrl());
            $io->writeln('Leave empty to delete.');
        }
        $url = $io->ask('Webhook URL address: ', null, function ($value) {
            if ($value) {
                Assertion::url($value);
            }
            return $value;
        });
        if ($url) {
            $webhook->setUrl($url);
            $accessToken = $io->ask('Access token: ', '');
            $webhook->setAccessToken($accessToken);
            $webhook->setRefreshToken($accessToken);
            $webhook->setExpiresAt(new \DateTime('+10 years'));
            $webhook->setFunctions(ChannelFunction::values());
            $webhook->setEnabled(true);
            $this->entityManager->persist($webhook);
            $this->entityManager->flush();
            $this->suplaServer->stateWebhookChanged();
            $io->success('Webhook has been saved.');
        } elseif ($webhook->getId()) {
            $this->entityManager->remove($webhook);
            $this->entityManager->flush();
            $this->suplaServer->stateWebhookChanged();
            $io->success('Webhook has been deleted.');
        }
        return 0;
    }
}
