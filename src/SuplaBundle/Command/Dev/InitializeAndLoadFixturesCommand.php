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

namespace SuplaBundle\Command\Dev;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class InitializeAndLoadFixturesCommand extends Command {
    private readonly string $env;

    public function __construct(ParameterBagInterface $parameters) {
        parent::__construct();
        $this->env = $parameters->get('kernel.environment');
    }

    protected function configure(): void {
        $this
            ->setName('supla:dev:dropAndLoadFixtures')
            ->setDescription('Purge database and load fixtures.');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output): int {
        if ($this->env !== 'dev') {
            $output->writeln('This command is only available in dev environment.');
            return self::FAILURE;
        }
        $this->getApplication()->setAutoExit(false);
        $this->getApplication()->run(new StringInput('doctrine:database:create --if-not-exists'), $output);
        $this->getApplication()->run(new StringInput('doctrine:database:drop --force --no-interaction'), $output);
        $this->getApplication()->run(new StringInput('doctrine:database:create --if-not-exists'), $output);
        $this->getApplication()->run(new StringInput('supla:initialize'), $output);
        $this->getApplication()->run(new StringInput('doctrine:fixtures:load --no-interaction --append -vvv'), $output);
        return self::SUCCESS;
    }
}
