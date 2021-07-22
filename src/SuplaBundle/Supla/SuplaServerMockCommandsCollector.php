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

namespace SuplaBundle\Supla;

use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class SuplaServerMockCommandsCollector extends DataCollector {
    const NAME = 'supla.supla_server_mock_data_collector';

    public function __construct() {
        $this->reset();
    }

    public function collect(Request $request, Response $response, Exception $exception = null) {
    }

    public function addCommand(string $command) {
        $this->data['commands'][] = $command;
    }

    public function getCommands(): array {
        return $this->data['commands'];
    }

    public function getName() {
        return self::NAME;
    }

    public function reset() {
        $this->data['commands'] = [];
    }
}
