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

use SuplaBundle\Enums\RgbwCommand;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateTranslationsCommand extends Command {
    use SuplaServerAware;

    protected function configure() {
        $this
            ->setName('supla:dev:generateTranslations')
            ->setDescription('Generates required translations from backend sources.');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $translations = [];
        $translations = array_merge($translations, array_map(function (RgbwCommand $command) {
            return "rgbwCommand_label_{$command->name}";
        }, RgbwCommand::cases()));
        $translations = array_map(fn(string $t) => "// i18n: ['$t']", $translations);
        file_put_contents(\AppKernel::VAR_PATH . '/local/translations.php', '<?php' . PHP_EOL . implode("\n", $translations) . PHP_EOL);
        return 0;
    }
}
