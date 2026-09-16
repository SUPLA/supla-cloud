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

namespace App\Model\UserConfigTranslator;

use App\Entity\HasUserConfig;
use App\Enums\ChannelFunction;
use Assert\Assert;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="ChannelConfigGeneralPurposeText",
 *   description="Config for `GENERAL_PURPOSE_TEXT` function.",
 *   @OA\Property(property="keepHistory", type="boolean"),
 *   @OA\Property(property="refreshIntervalMs", type="integer"),
 * )
 */
class GeneralPurposeTextConfigTranslator extends UserConfigTranslator {
    public function getConfig(HasUserConfig $subject): array {
        return [
            'keepHistory' => $subject->getUserConfigValue('keepHistory', true),
            'refreshIntervalMs' => $subject->getUserConfigValue('refreshIntervalMs', 0),
        ];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('keepHistory', $config)) {
            $subject->setUserConfigValue('keepHistory', filter_var($config['keepHistory'], FILTER_VALIDATE_BOOLEAN));
        }
        if (array_key_exists('refreshIntervalMs', $config)) {
            Assert::that($config['refreshIntervalMs'], null, 'refreshIntervalMs')->integer()->between(0, 65535);
            $subject->setUserConfigValue('refreshIntervalMs', intval($config['refreshIntervalMs']));
        }
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::GENERAL_PURPOSE_TEXT,
        ]);
    }

    public function clearConfig(HasUserConfig $subject): void {
        $subject->setUserConfig([]);
    }
}
