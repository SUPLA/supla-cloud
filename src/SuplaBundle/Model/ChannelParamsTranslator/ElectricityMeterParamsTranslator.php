<?php

namespace SuplaBundle\Model\ChannelParamsTranslator;

use Assert\Assertion;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\IODeviceChannel;
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
class ElectricityMeterParamsTranslator implements ChannelParamTranslator {
    use FixedRangeParamsTranslator;

    public function getConfigFromParams(IODeviceChannel $channel): array {
        return [
            'pricePerUnit' => NumberUtils::maximumDecimalPrecision($channel->getParam2() / 10000, 4),
            'currency' => $channel->getTextParam1() ?: null,
            'resetCountersAvailable' => ChannelFunctionBitsFlags::RESET_COUNTERS_ACTION_AVAILABLE()->isSupported($channel->getFlags()),
            'countersAvailable' => ($channel->getProperties()['countersAvailable'] ?? []) ?: [],
            'electricityMeterInitialValues' => new JsonArrayObject($channel->getUserConfig()['electricityMeterInitialValues'] ?? []),
            'addToHistory' => $channel->getUserConfigValue('addToHistory', false),
            'lowerVoltageThreshold' => $channel->getUserConfigValue('lowerVoltageThreshold'),
            'upperVoltageThreshold' => $channel->getUserConfigValue('upperVoltageThreshold'),
            'disabledPhases' => $channel->getUserConfigValue('disabledPhases', []),
        ];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        if (array_key_exists('pricePerUnit', $config)) {
            $channel->setParam2(intval($this->getValueInRange($config['pricePerUnit'], 0, 1000) * 10000));
        }
        if (array_key_exists('currency', $config)) {
            $currency = $config['currency'];
            if (!$currency || preg_match('/^[A-Z]{3}$/', $currency)) {
                $channel->setTextParam1($currency);
            }
        }
        if (array_key_exists('electricityMeterInitialValues', $config)) {
            $values = is_array($config['electricityMeterInitialValues']) ? $config['electricityMeterInitialValues'] : [];
            $countersAvailable = $channel->getProperties()['countersAvailable'] ?? [];
            $initialValues = $channel->getUserConfig()['electricityMeterInitialValues'] ?? [];
            foreach ($values as $counterName => $initialValue) {
                Assertion::inArray($counterName, $countersAvailable);
                $initialValue = $this->getValueInRange($initialValue, -100000000, 100000000); // 100 mln
                $initialValue = NumberUtils::maximumDecimalPrecision($initialValue, 3);
                $initialValues[$counterName] = $initialValue;
            }
            $channel->setUserConfigValue('electricityMeterInitialValues', $initialValues);
        }
        if (array_key_exists('addToHistory', $config)) {
            $channel->setUserConfigValue('addToHistory', boolval($config['addToHistory']));
        }
        if (array_key_exists('lowerVoltageThreshold', $config)) {
            $threshold = $config['lowerVoltageThreshold'] ? $this->getValueInRange($config['lowerVoltageThreshold'], 5, 240) : null;
            $channel->setUserConfigValue('lowerVoltageThreshold', $threshold);
        }
        if (array_key_exists('upperVoltageThreshold', $config)) {
            $threshold = $config['upperVoltageThreshold'] ? $this->getValueInRange($config['upperVoltageThreshold'], 10, 500) : null;
            $channel->setUserConfigValue('upperVoltageThreshold', $threshold);
        }
        if (array_key_exists('disabledPhases', $config)) {
            Assertion::isArray($config['disabledPhases'], 'disabledPhases config value must be an array');
            Assertion::allInArray($config['disabledPhases'], [1, 2, 3], 'disabledPhases may only contain values: 1, 2, 3');
            $disabledPhases = array_values(array_unique($config['disabledPhases']));
            Assertion::lessThan(count($disabledPhases), 3, 'You must leave at least one phase enabled.'); // i18n
            $channel->setUserConfigValue('disabledPhases', $disabledPhases);
        }
        $lowerVoltageThreshold = $channel->getUserConfigValue('lowerVoltageThreshold');
        $upperVoltageThreshold = $channel->getUserConfigValue('upperVoltageThreshold');
        if ($lowerVoltageThreshold && $upperVoltageThreshold) {
            Assertion::lessThan($lowerVoltageThreshold, $upperVoltageThreshold);
        }
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::ELECTRICITYMETER,
        ]);
    }
}
