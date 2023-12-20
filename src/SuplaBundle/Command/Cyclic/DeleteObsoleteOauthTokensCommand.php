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
use FOS\OAuthServerBundle\Model\AccessTokenManagerInterface;
use FOS\OAuthServerBundle\Model\AuthCodeManagerInterface;
use SuplaBundle\Entity\Main\OAuth\RefreshToken;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteObsoleteOauthTokensCommand extends AbstractCyclicCommand {
    /** @var AccessTokenManagerInterface */
    private $accessTokenManager;
    /** @var AuthCodeManagerInterface */
    private $authCodeManager;
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        AccessTokenManagerInterface $accessTokenManager,
        AuthCodeManagerInterface $authCodeManager,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->accessTokenManager = $accessTokenManager;
        $this->authCodeManager = $authCodeManager;
        $this->entityManager = $entityManager;
    }

    protected function configure() {
        $this
            ->setName('supla:clean:obsolete-oauth-tokens')
            ->setDescription('Delete expired Access/Refresh OAuth tokens and Auth Codes.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        foreach ([$this->accessTokenManager, $this->authCodeManager] as $manager) {
            $result = $manager->deleteExpired();
            $output->writeln(sprintf('Removed <info>%d</info> items from <comment>%s</comment> storage.', $result, get_class($manager)));
        }
        $result = $this->entityManager->getRepository(RefreshToken::class)->createQueryBuilder('t')
            ->delete()->where('t.expiresAt < ?1')->setParameters([1 => strtotime('-7 days')])
            ->getQuery()->execute();
        $output->writeln(sprintf('Removed <info>%d</info> items from <comment>RefreshToken</comment> storage.', $result));
        return 0;
    }

    protected function getIntervalInMinutes(): int {
        return 60; // every hour
    }
}
