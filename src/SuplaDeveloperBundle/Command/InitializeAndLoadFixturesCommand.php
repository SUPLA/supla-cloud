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

namespace SuplaDeveloperBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitializeAndLoadFixturesCommand extends Command {
    protected function configure() {
        $this
            ->setName('supla:dev:dropAndLoadFixtures')
            ->setDescription('Purge database and load fixtures.');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->runCommand('doctrine:database:create', ['--if-not-exists' => true, '--no-interaction' => true], $output);
        $this->runCommand('doctrine:schema:drop', ['--force' => true, '--no-interaction' => true], $output);
        $this->runCommand('doctrine:schema:create', ['--no-interaction' => true], $output);
        $this->runCommand('doctrine:fixtures:load', ['--no-interaction' => true, '--append' => true], $output);
    }

    private function runCommand(string $name, array $arguments, OutputInterface $output) {
        $command = $this->getApplication()->find($name);
        $input = new ArrayInput($arguments);
        return $command->run($input, $output);
    }
}
