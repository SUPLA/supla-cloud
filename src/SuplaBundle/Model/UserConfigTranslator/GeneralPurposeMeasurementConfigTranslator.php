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
use SuplaBundle\Utils\NumberUtils;

/**
 * @OA\Schema(schema="ChannelConfigGeneralPurposeMeasurement", description="Config for `GENERAL_PURPOSE_MEASUREMENT` function.",
 *   @OA\Property(property="valueDivider", type="number"),
 *   @OA\Property(property="valueMultiplier", type="number"),
 *   @OA\Property(property="valueAdded", type="number"),
 *   @OA\Property(property="valuePrecision", type="integer"),
 *   @OA\Property(property="unitBeforeValue", type="string"),
 *   @OA\Property(property="unitAfterValue", type="string"),
 *   @OA\Property(property="keepHistory", type="boolean"),
 *   @OA\Property(property="chartType", type="string", enum={"LINEAR", "BAR", "CANDLE"}),
 *   @OA\Property(property="defaults", type="object",
 *     @OA\Property(property="valueDivider", type="number"),
 *     @OA\Property(property="valueMultiplier", type="number"),
 *     @OA\Property(property="valueAdded", type="number"),
 *     @OA\Property(property="valuePrecision", type="integer"),
 *     @OA\Property(property="unitBeforeValue", type="string"),
 *     @OA\Property(property="unitAfterValue", type="string"),
 *   )
 * )
 */
class GeneralPurposeMeasurementConfigTranslator extends UserConfigTranslator {
    use FixedRangeParamsTranslator;

    private const CHART_TYPES = ['LINEAR', 'BAR', 'CANDLE'];

    public function getConfig(HasUserConfig $subject): array {
        $config = $subject->getUserConfig();
        if (array_key_exists('valueMultiplier', $config)) {
            return [
                'valueDivider' => NumberUtils::maximumDecimalPrecision($subject->getUserConfigValue('valueDivider', 0) / 1000 ?: null, 3),
                'valueMultiplier' => NumberUtils::maximumDecimalPrecision($subject->getUserConfigValue('valueMultiplier', 0) / 1000 ?: null, 3),
                'valueAdded' => NumberUtils::maximumDecimalPrecision($subject->getUserConfigValue('valueAdded', 0) / 1000 ?: null, 3),
                'valuePrecision' => $subject->getUserConfigValue('valuePrecision'),
                'unitBeforeValue' => $subject->getUserConfigValue('unitBeforeValue'),
                'unitAfterValue' => $subject->getUserConfigValue('unitAfterValue'),
                'keepHistory' => $subject->getUserConfigValue('keepHistory', false),
                'chartType' => $subject->getUserConfigValue('chartType', self::CHART_TYPES[0]),
                'defaults' => [
                    'valueDivider' => NumberUtils::maximumDecimalPrecision($subject->getProperty('defaultValueDivider', 0) / 1000 ?: null, 3),
                    'valueMultiplier' => NumberUtils::maximumDecimalPrecision($subject->getProperty('defaultValueMultiplier', 0) / 1000 ?: null, 3),
                    'valueAdded' => NumberUtils::maximumDecimalPrecision($subject->getProperty('defaultValueAdded', 0) / 1000 ?: null, 3),
                    'valuePrecision' => $subject->getProperty('defaultValuePrecision', 0),
                    'unitBeforeValue' => $subject->getProperty('defaultUnitBeforeValue'),
                    'unitAfterValue' => $subject->getProperty('defaultUnitAfterValue'),
                ],
            ];
        } else {
            return [
                'waitingForConfigInit' => true,
            ];
        }
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('valueDivider', $config)) {
            if ($config['valueDivider']) {
                Assert::that($config['valueDivider'], null, 'valueDivider')->numeric()->between(-1000, 1000);
                $subject->setUserConfigValue('valueDivider', intval($config['valueDivider'] * 1000));
            } else {
                $subject->setUserConfigValue('valueDivider', null);
            }
        }
        if (array_key_exists('valueMultiplier', $config)) {
            if ($config['valueMultiplier']) {
                Assert::that($config['valueMultiplier'], null, 'valueMultiplier')->numeric()->between(-1000, 1000);
                $subject->setUserConfigValue('valueMultiplier', intval($config['valueMultiplier'] * 1000));
            } else {
                $subject->setUserConfigValue('valueMultiplier', null);
            }
        }
        if (array_key_exists('valueAdded', $config)) {
            if ($config['valueAdded']) {
                Assert::that($config['valueAdded'], null, 'valueAdded')->numeric()->between(-100000000, 100000000);
                $subject->setUserConfigValue('valueAdded', intval($config['valueAdded'] * 1000));
            } else {
                $subject->setUserConfigValue('valueAdded', null);
            }
        }
        if (array_key_exists('valuePrecision', $config)) {
            if ($config['valuePrecision']) {
                Assert::that($config['valuePrecision'], null, 'valuePrecision')->integer()->between(0, 10);
                $subject->setUserConfigValue('valuePrecision', $config['valuePrecision']);
            } else {
                $subject->setUserConfigValue('valuePrecision', null);
            }
        }
        if (array_key_exists('unitBeforeValue', $config)) {
            if ($config['unitBeforeValue']) {
                Assert::that($config['unitBeforeValue'], null, 'unitBeforeValue')->string()->maxLength(12);
                $subject->setUserConfigValue('unitBeforeValue', $config['unitBeforeValue']);
            } else {
                $subject->setUserConfigValue('unitBeforeValue', null);
            }
        }
        if (array_key_exists('unitAfterValue', $config)) {
            if ($config['unitAfterValue']) {
                Assert::that($config['unitAfterValue'], null, 'unitAfterValue')->string()->maxLength(12);
                $subject->setUserConfigValue('unitAfterValue', $config['unitAfterValue']);
            } else {
                $subject->setUserConfigValue('unitAfterValue', null);
            }
        }
        if (array_key_exists('keepHistory', $config)) {
            $subject->setUserConfigValue('keepHistory', filter_var($config['keepHistory'], FILTER_VALIDATE_BOOLEAN));
        }
        if (array_key_exists('chartType', $config)) {
            Assert::that($config['chartType'], null, 'chartType')->string()->inArray(self::CHART_TYPES);
            $subject->setUserConfigValue('chartType', $config['chartType']);
        }
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
        ]);
    }

    public function clearConfig(HasUserConfig $subject): void {
        $subject->setUserConfig([]);
    }
}
