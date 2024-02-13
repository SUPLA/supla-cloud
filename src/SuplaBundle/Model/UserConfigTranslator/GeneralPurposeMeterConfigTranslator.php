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

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assert;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionBitsFlags;

/**
 * @OA\Schema(schema="ChannelConfigGeneralPurposeMeter", description="Config for `GENERAL_PURPOSE_METER` function.",
 *   allOf={
 *     @OA\Schema(ref="#/components/schemas/ChannelConfigGeneralPurposeMeasurement"),
 *     @OA\Schema(
 *       @OA\Property(property="includeValueAddedInHistory", type="boolean"),
 *       @OA\Property(property="fillMissingData", type="boolean"),
 *       @OA\Property(property="allowCounterReset", type="boolean"),
 *       @OA\Property(property="alwaysDecrement", type="boolean"),
 *    ),
 *   }
 * )
 */
class GeneralPurposeMeterConfigTranslator extends GeneralPurposeMeasurementConfigTranslator {
    use FixedRangeParamsTranslator;

    private const COUNTER_TYPES = ['ALWAYS_INCREMENT', 'ALWAYS_DECREMENT', 'INCREMENT_AND_DECREMENT'];

    public function getConfig(HasUserConfig $subject): array {
        $config = parent::getConfig($subject);
        if (array_key_exists('valueMultiplier', $config)) {
            $config['includeValueAddedInHistory'] = $subject->getUserConfigValue('includeValueAddedInHistory');
            $config['fillMissingData'] = $subject->getUserConfigValue('fillMissingData');
            $config['counterType'] = $subject->getUserConfigValue('counterType');
            $config['resetCountersAvailable'] = ChannelFunctionBitsFlags::RESET_COUNTERS_ACTION_AVAILABLE()->isSupported($subject->getFlags());
        }
        return $config;
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        parent::setConfig($subject, $config);
        if (array_key_exists('includeValueAddedInHistory', $config)) {
            $subject->setUserConfigValue('includeValueAddedInHistory', filter_var($config['includeValueAddedInHistory'], FILTER_VALIDATE_BOOLEAN));
        }
        if (array_key_exists('fillMissingData', $config)) {
            $subject->setUserConfigValue('fillMissingData', filter_var($config['fillMissingData'], FILTER_VALIDATE_BOOLEAN));
        }
        if (array_key_exists('counterType', $config)) {
            Assert::that($config['counterType'], null, 'counterType')->string()->inArray(self::COUNTER_TYPES);
            $subject->setUserConfigValue('counterType', $config['counterType']);
        }
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::GENERAL_PURPOSE_METER,
        ]);
    }
}
