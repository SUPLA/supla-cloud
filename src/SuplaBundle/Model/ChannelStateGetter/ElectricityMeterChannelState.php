<?php

namespace SuplaBundle\Model\ChannelStateGetter;

use Assert\Assertion;
use OpenApi\Annotations as OA;
use SuplaBundle\Enums\ElectricityMeterSupportBits;
use SuplaBundle\Utils\NumberUtils;
use SuplaBundle\Utils\StringUtils;

/**
 * @OA\Schema(schema="ChannelStateElectricityMeterPhase",
 *      @OA\Property(property="number", type="integer"),
 *      @OA\Property(property="frequency", type="number"),
 *      @OA\Property(property="voltage", type="number"),
 *      @OA\Property(property="current", type="number"),
 *      @OA\Property(property="powerActive", type="number"),
 *      @OA\Property(property="powerReactive", type="number"),
 *      @OA\Property(property="powerApparent", type="number"),
 *      @OA\Property(property="powerFactor", type="number"),
 *      @OA\Property(property="phaseAngle", type="number"),
 *      @OA\Property(property="totalForwardActiveEnergy", type="number"),
 *      @OA\Property(property="totalReverseActiveEnergy", type="number"),
 *      @OA\Property(property="totalForwardReactiveEnergy", type="number"),
 *      @OA\Property(property="totalReverseReactiveEnergy", type="number"),
 * )
 * @OA\Schema(schema="ChannelStateElectricityMeter",
 *     description="State of `ELECTRICITYMETER`",
 *     @OA\Property(property="connected", type="boolean"),
 *     @OA\Property(property="support", type="integer", description="A bitmask indicating which measurements are supported by the electricity meter. See https://github.com/SUPLA/supla-cloud/blob/master/src/SuplaBundle/Enums/ElectricityMeterSupportBits.php for more info. All not supported measurements will not be present in the response."),
 *     @OA\Property(property="totalCost", type="number"),
 *     @OA\Property(property="pricePerUnit", type="number"),
 *     @OA\Property(property="currency", type="string"),
 *     @OA\Property(property="phases", type="array", @OA\Items(ref="#/components/schemas/ChannelStateElectricityMeterPhase")),
 * )
 */
class ElectricityMeterChannelState {
    private $state = [];

    /**
     * Multipliers of the values returned by the SUPLA-Server.
     * The order of these elements IS important, because it represents the order in which the values are returned from the SUPLA-Server.
     * @var array
     */
    private static $SUPLA_SERVER_VALUES_MULTIPLIERS = [
        'voltage' => 100,
        'current' => 1000,
        'powerActive' => 100000,
        'powerReactive' => 100000,
        'powerApparent' => 100000,
        'powerFactor' => 1000,
        'phaseAngle' => 10,
        'totalForwardActiveEnergy' => 100000,
        'totalReverseActiveEnergy' => 100000,
        'totalForwardReactiveEnergy' => 100000,
        'totalReverseReactiveEnergy' => 100000,
    ];

    public function __construct(array $valuesFromSuplaServer) {
        $this->state['support'] = intval(array_shift($valuesFromSuplaServer));
        $frequency = NumberUtils::maximumDecimalPrecision(array_shift($valuesFromSuplaServer) * 0.01, 2);
        $this->state['currency'] = array_pop($valuesFromSuplaServer);
        $this->state['pricePerUnit'] = NumberUtils::maximumDecimalPrecision(array_pop($valuesFromSuplaServer) * 0.0001, 4);
        $this->state['totalCost'] = NumberUtils::maximumDecimalPrecision(array_pop($valuesFromSuplaServer) * 0.01, 2);
        Assertion::eq(0, count($valuesFromSuplaServer) % count(self::$SUPLA_SERVER_VALUES_MULTIPLIERS), 'Invalid EM state.');
        $numberOfPhases = count($valuesFromSuplaServer) / count(self::$SUPLA_SERVER_VALUES_MULTIPLIERS);
        $measurementIndex = 0;
        $this->state['phases'] = [];
        for ($phase = 0; $phase < $numberOfPhases; $phase++) {
            $this->state['phases'][$phase] = ['number' => ($phase + 1)];
            if ($this->isSupported('frequency')) {
                $this->state['phases'][$phase]['frequency'] = $frequency;
            }
        }
        foreach (self::$SUPLA_SERVER_VALUES_MULTIPLIERS as $name => $multiplier) {
            if ($this->isSupported($name)) {
                for ($phase = 0; $phase < $numberOfPhases; $phase++) {
                    $value = ($valuesFromSuplaServer[$measurementIndex + $phase] ?? 0) / $multiplier;
                    $this->state['phases'][$phase][$name] =
                        NumberUtils::maximumDecimalPrecision($value, round(log10($multiplier)));
                }
            }
            $measurementIndex += $numberOfPhases;
        }
    }

    private function isSupported(string $name): bool {
        if ($name === 'current') {
            return $this->state['support'] & ElectricityMeterSupportBits::CURRENT
                || $this->state['support'] & ElectricityMeterSupportBits::CURRENT_OVER64A;
        }
        $bitName = StringUtils::camelCaseToSnakeCase($name);
        return $this->state['support'] & ElectricityMeterSupportBits::$bitName()->getValue();
    }

    public function toArray(): array {
        return $this->state;
    }
}
