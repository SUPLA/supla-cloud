<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assertion;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionBitsFlags;
use SuplaBundle\Utils\JsonArrayObject;
use SuplaBundle\Utils\NumberUtils;

/**
 * @OA\Schema(schema="ChannelConfigElectricityMeter",
 *     description="Config for `ELECTRICITYMETER`",
 *     @OA\Property(property="countersAvailable", type="array", readOnly=true, description="List of available counters supported by this channel.", @OA\Items(type="string")),
 *     @OA\Property(property="resetCountersAvailable", type="boolean", readOnly=true),
 *     @OA\Property(property="pricePerUnit", type="number"),
 *     @OA\Property(property="currency", type="string"),
 *     @OA\Property(property="electricityMeterInitialValues", type="object"),
 *     @OA\Property(property="relatedChannelId", type="integer"),
 *     @OA\Property(property="addToHistory", type="boolean"),
 *     @OA\Property(property="lowerVoltageThreshold", type="number"),
 *     @OA\Property(property="upperVoltageThreshold", type="number"),
 * )
 */
class ElectricityMeterParamsTranslator implements UserConfigTranslator {
    use FixedRangeParamsTranslator;

    public function getConfig(HasUserConfig $subject): array {
        return [
            'pricePerUnit' => NumberUtils::maximumDecimalPrecision($subject->getParam2() / 10000, 4),
            'currency' => $subject->getTextParam1() ?: null,
            'resetCountersAvailable' => ChannelFunctionBitsFlags::RESET_COUNTERS_ACTION_AVAILABLE()->isSupported($subject->getFlags()),
            'countersAvailable' => ($subject->getProperties()['countersAvailable'] ?? []) ?: [],
            'electricityMeterInitialValues' => new JsonArrayObject($subject->getUserConfig()['electricityMeterInitialValues'] ?? []),
            'addToHistory' => $subject->getUserConfigValue('addToHistory', false),
            'lowerVoltageThreshold' => $subject->getUserConfigValue('lowerVoltageThreshold'),
            'upperVoltageThreshold' => $subject->getUserConfigValue('upperVoltageThreshold'),
            'disabledPhases' => $subject->getUserConfigValue('disabledPhases', []),
            'enabledPhases' => array_values(array_diff(
                $this->getAvailablePhases($subject),
                $subject->getUserConfigValue('disabledPhases', [])
            )),
            'availablePhases' => $this->getAvailablePhases($subject),
        ];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('pricePerUnit', $config)) {
            $subject->setParam2(intval($this->getValueInRange($config['pricePerUnit'], 0, 1000) * 10000));
        }
        if (array_key_exists('currency', $config)) {
            $currency = $config['currency'];
            if (!$currency || preg_match('/^[A-Z]{3}$/', $currency)) {
                $subject->setTextParam1($currency);
            }
        }
        if (array_key_exists('electricityMeterInitialValues', $config)) {
            $values = is_array($config['electricityMeterInitialValues']) ? $config['electricityMeterInitialValues'] : [];
            $initialValues = $this->getInitialValues($values, $subject);
            $subject->setUserConfigValue('electricityMeterInitialValues', $initialValues);
        }
        if (array_key_exists('addToHistory', $config)) {
            $subject->setUserConfigValue('addToHistory', boolval($config['addToHistory']));
        }
        if (array_key_exists('lowerVoltageThreshold', $config)) {
            $threshold = $config['lowerVoltageThreshold'] ? $this->getValueInRange($config['lowerVoltageThreshold'], 5, 240) : null;
            $subject->setUserConfigValue('lowerVoltageThreshold', $threshold);
        }
        if (array_key_exists('upperVoltageThreshold', $config)) {
            $threshold = $config['upperVoltageThreshold'] ? $this->getValueInRange($config['upperVoltageThreshold'], 10, 500) : null;
            $subject->setUserConfigValue('upperVoltageThreshold', $threshold);
        }
        if (array_key_exists('disabledPhases', $config)) {
            $disabledPhases = $config['disabledPhases'];
            if (!$disabledPhases) {
                $disabledPhases = [];
            }
            Assertion::isArray($disabledPhases, 'disabledPhases config value must be an array');
            Assertion::allInArray($disabledPhases, $this->getAvailablePhases($subject), 'disabledPhases may only contain available phases');
            $disabledPhases = array_values(array_unique($disabledPhases));
            Assertion::lessThan(count($disabledPhases), 3, 'You must leave at least one phase enabled.'); // i18n
            $subject->setUserConfigValue('disabledPhases', $disabledPhases);
        }
        $lowerVoltageThreshold = $subject->getUserConfigValue('lowerVoltageThreshold');
        $upperVoltageThreshold = $subject->getUserConfigValue('upperVoltageThreshold');
        if ($lowerVoltageThreshold && $upperVoltageThreshold) {
            Assertion::lessThan($lowerVoltageThreshold, $upperVoltageThreshold);
        }
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::ELECTRICITYMETER,
        ]);
    }

    private function getAvailablePhases(IODeviceChannel $channel): array {
        $availablePhases = [];
        if (!ChannelFunctionBitsFlags::ELECTRICITY_METER_PHASE1_UNSUPPORTED()->isOn($channel->getFlags())) {
            $availablePhases[] = 1;
        }
        if (!ChannelFunctionBitsFlags::ELECTRICITY_METER_PHASE2_UNSUPPORTED()->isOn($channel->getFlags())) {
            $availablePhases[] = 2;
        }
        if (!ChannelFunctionBitsFlags::ELECTRICITY_METER_PHASE3_UNSUPPORTED()->isOn($channel->getFlags())) {
            $availablePhases[] = 3;
        }
        return $availablePhases;
    }

    private function getInitialValues(array $values, IODeviceChannel $channel): array {
        $countersAvailable = $channel->getProperties()['countersAvailable'] ?? [];
        $initialValues = $channel->getUserConfig()['electricityMeterInitialValues'] ?? [];
        $enabledPhases = $this->getAvailablePhases($channel);
        foreach ($values as $counterName => $initialValue) {
            Assertion::inArray($counterName, $countersAvailable);
            if (is_array($initialValue)) {
                Assertion::notInArray(
                    $counterName,
                    ['forwardActiveEnergyBalanced', 'reverseActiveEnergyBalanced'],
                    'Advanced mode is unsupported for this counter.'
                );
                $valueForEachPhase = [];
                foreach ($enabledPhases as $enabledPhase) {
                    $initialValueForPhase = $this->getValueInRange($initialValue[$enabledPhase] ?? 0, -100000000, 100000000, 0); // 100 mln
                    $initialValueForPhase = NumberUtils::maximumDecimalPrecision($initialValueForPhase, 3);
                    $valueForEachPhase[$enabledPhase] = $initialValueForPhase;
                }
                $initialValues[$counterName] = $valueForEachPhase;
            } else {
                $initialValue = $this->getValueInRange($initialValue, -100000000, 100000000, 0); // 100 mln
                $initialValue = NumberUtils::maximumDecimalPrecision($initialValue, 3);
                $initialValues[$counterName] = $initialValue;
            }
        }
        return $initialValues;
    }
}
