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
 *   @OA\Property(property="relatedChannelId", type="integer"),
 * )
 */
class ImpulseCounterParamsTranslator extends UserConfigTranslator {
    use FixedRangeParamsTranslator;

    public function getConfig(HasUserConfig $subject): array {
        return [
            'pricePerUnit' => NumberUtils::maximumDecimalPrecision($subject->getParam2() / 10000, 4),
            'impulsesPerUnit' => $subject->getParam3(),
            'currency' => $subject->getTextParam1() ?: null,
            'unit' => $subject->getTextParam2() ?: null,
            'initialValue' => $subject->getUserConfigValue('initialValue', 0),
            'addToHistory' => $subject->getUserConfigValue('addToHistory', false),
            'resetCountersAvailable' => ChannelFunctionBitsFlags::RESET_COUNTERS_ACTION_AVAILABLE()->isSupported($subject->getFlags()),
        ];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('initialValue', $config)) {
            $initialValue = NumberUtils::maximumDecimalPrecision($this->getValueInRange($config['initialValue'], -100000000, 100000000), 3);
            $subject->setUserConfigValue('initialValue', $initialValue);
        }
        if (array_key_exists('addToHistory', $config)) {
            $subject->setUserConfigValue('addToHistory', boolval($config['addToHistory']));
        }
        if (array_key_exists('pricePerUnit', $config)) {
            $value = $this->getValueInRange($config['pricePerUnit'], 0, 1000) * 10000;
            $subject->setParam2($value);
            $subject->setUserConfigValue('pricePerUnit', $value);
        }
        if (array_key_exists('impulsesPerUnit', $config)) {
            $value = $this->getValueInRange($config['impulsesPerUnit'], 0, 1000000);
            $subject->setParam3($value);
            $subject->setUserConfigValue('impulsesPerUnit', $value);
        }
        if (array_key_exists('currency', $config)) {
            $currency = $config['currency'];
            if (!$currency || preg_match('/^[A-Z]{3}$/', $currency)) {
                $subject->setTextParam1($currency);
                $subject->setUserConfigValue('currency', $currency ?: null);
            }
        }
        if (array_key_exists('unit', $config)) {
            $value = $config['unit'] ?? '';
            Assert::that($value, null, 'unit')->string()->maxLength(8, null, null, 'ASCII');
            $subject->setTextParam2($value);
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
