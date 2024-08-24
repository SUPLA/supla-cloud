<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assert;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionBitsFlags;
use SuplaBundle\Utils\NumberUtils;

/**
 * @OA\Schema(schema="ChannelConfigImpulseCounter", description="Config for `IC_*` functions.",
 *   @OA\Property(property="resetCountersAvailable", type="boolean"),
 *   @OA\Property(property="pricePerUnit", type="number"),
 *   @OA\Property(property="impulsesPerUnit", type="integer"),
 *   @OA\Property(property="currency", type="string"),
 *   @OA\Property(property="unit", type="string"),
 *   @OA\Property(property="initialValue", type="integer"),
 *   @OA\Property(property="relatedRelayChannelId", type="integer"),
 *   @OA\Property(property="ocrSettings", type="object"),
 * )
 */
class ImpulseCounterUserConfigTranslator extends UserConfigTranslator {
    use FixedRangeParamsTranslator;

    public function getConfig(HasUserConfig $subject): array {
        return [
            'pricePerUnit' => NumberUtils::maximumDecimalPrecision($subject->getUserConfigValue('pricePerUnit', 0) / 10000, 4),
            'impulsesPerUnit' => $subject->getUserConfigValue('impulsesPerUnit'),
            'currency' => $subject->getUserConfigValue('currency'),
            'unit' => $subject->getUserConfigValue('unit'),
            'initialValue' => NumberUtils::maximumDecimalPrecision($subject->getUserConfigValue('initialValue', 0) / 1000, 3),
            'addToHistory' => $subject->getUserConfigValue('addToHistory', false),
            'resetCountersAvailable' => ChannelFunctionBitsFlags::RESET_COUNTERS_ACTION_AVAILABLE()->isSupported($subject->getFlags()),
        ];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('initialValue', $config)) {
            $initialValue = NumberUtils::maximumDecimalPrecision($this->getValueInRange($config['initialValue'], -100000000, 100000000), 3);
            $initialValue *= 1000;
            $subject->setUserConfigValue('initialValue', round($initialValue));
        }
        if (array_key_exists('addToHistory', $config)) {
            $subject->setUserConfigValue('addToHistory', boolval($config['addToHistory']));
        }
        if (array_key_exists('pricePerUnit', $config)) {
            $value = $this->getValueInRange($config['pricePerUnit'], 0, 1000) * 10000;
            $subject->setUserConfigValue('pricePerUnit', round($value));
        }
        if (array_key_exists('impulsesPerUnit', $config)) {
            $value = $this->getValueInRange($config['impulsesPerUnit'], 0, 1000000);
            $subject->setUserConfigValue('impulsesPerUnit', round($value));
        }
        if (array_key_exists('currency', $config)) {
            $currency = $config['currency'];
            if (!$currency || preg_match('/^[A-Z]{3}$/', $currency)) {
                $subject->setUserConfigValue('currency', $currency ?: null);
            }
        }
        if (array_key_exists('unit', $config)) {
            $value = $config['unit'] ?? '';
            Assert::that($value, null, 'unit')->string()->maxLength(8, null, null, 'ASCII');
            $subject->setUserConfigValue('unit', $value ?: null);
        }
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::IC_ELECTRICITYMETER,
            ChannelFunction::IC_GASMETER,
            ChannelFunction::IC_WATERMETER,
            ChannelFunction::IC_HEATMETER,
        ]);
    }
}
