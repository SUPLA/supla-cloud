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

namespace App\Model\ChannelStateGetter;

use App\Entity\Main\IODeviceChannel;
use App\Enums\ChannelFunction;
use App\Supla\SuplaServerAware;

class GeneralPurposeTextChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        $response = $this->suplaServer->getRawValue('GPT', $channel);
        if (!is_string($response)) {
            return ['value' => null];
        }

        if (str_ends_with($response, "\n")) {
            $response = substr($response, 0, -1);
            if (str_ends_with($response, "\r")) {
                $response = substr($response, 0, -1);
            }
        }

        $base64Prefix = 'VALUE-B64:';
        $prefix = 'VALUE:';
        if (str_starts_with($response, $base64Prefix)) {
            $value = base64_decode(substr($response, strlen($base64Prefix)), true);
            if ($value === false) {
                throw new \UnexpectedValueException(
                    'Invalid Base64 response for general-purpose text channel.'
                );
            }
        } elseif (str_starts_with($response, $prefix)) {
            $value = substr($response, strlen($prefix));
        } else {
            $value = null;
        }
        return ['value' => $value];
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::GENERAL_PURPOSE_TEXT(),
        ];
    }
}
