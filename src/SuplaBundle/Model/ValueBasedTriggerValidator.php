<?php

namespace SuplaBundle\Model;

use Assert\Assertion;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

/**
 * @OA\Schema(schema="ReactionTrigger", description="Reaction trigger.",
 *   @OA\Property(property="on_change_to", type="object",
 *     oneOf={
 *       @OA\Schema(ref="#/components/schemas/ReactionTriggerLt"),
 *       @OA\Schema(ref="#/components/schemas/ReactionTriggerLe"),
 *       @OA\Schema(ref="#/components/schemas/ReactionTriggerGt"),
 *       @OA\Schema(ref="#/components/schemas/ReactionTriggerGe"),
 *       @OA\Schema(ref="#/components/schemas/ReactionTriggerEq"),
 *       @OA\Schema(ref="#/components/schemas/ReactionTriggerNe"),
 *     }
 *   )
 * )
 * @OA\Schema(schema="ReactionTriggerFieldNames", type="string", example="temperature", enum={"temperature", "humidity"})
 */
class ValueBasedTriggerValidator {
    private const FIELD_NAMES = [
        'temperature' => [ChannelFunction::THERMOMETER, ChannelFunction::HUMIDITYANDTEMPERATURE],
        'humidity' => [ChannelFunction::HUMIDITY, ChannelFunction::HUMIDITYANDTEMPERATURE],
        'voltage_avg' => [ChannelFunction::ELECTRICITYMETER],
        'voltage1' => [ChannelFunction::ELECTRICITYMETER],
        'voltage2' => [ChannelFunction::ELECTRICITYMETER],
        'voltage3' => [ChannelFunction::ELECTRICITYMETER],
        'current_sum' => [ChannelFunction::ELECTRICITYMETER],
        'current1' => [ChannelFunction::ELECTRICITYMETER],
        'current2' => [ChannelFunction::ELECTRICITYMETER],
        'current3' => [ChannelFunction::ELECTRICITYMETER],
        'power_active_sum' => [ChannelFunction::ELECTRICITYMETER],
        'power_active1' => [ChannelFunction::ELECTRICITYMETER],
        'power_active2' => [ChannelFunction::ELECTRICITYMETER],
        'power_active3' => [ChannelFunction::ELECTRICITYMETER],
        'power_reactive_sum' => [ChannelFunction::ELECTRICITYMETER],
        'power_reactive1' => [ChannelFunction::ELECTRICITYMETER],
        'power_reactive2' => [ChannelFunction::ELECTRICITYMETER],
        'power_reactive3' => [ChannelFunction::ELECTRICITYMETER],
        'power_apparent_sum' => [ChannelFunction::ELECTRICITYMETER],
        'power_apparent1' => [ChannelFunction::ELECTRICITYMETER],
        'power_apparent2' => [ChannelFunction::ELECTRICITYMETER],
        'power_apparent3' => [ChannelFunction::ELECTRICITYMETER],
    ];

    private const THRESHOLD_SUPPORT = [
        ChannelFunction::THERMOMETER,
        ChannelFunction::HUMIDITYANDTEMPERATURE,
        ChannelFunction::HUMIDITY,
        ChannelFunction::DEPTHSENSOR,
        ChannelFunction::ELECTRICITYMETER,
    ];

    public function validate(IODeviceChannel $channel, array $trigger): void {
        Assertion::keyExists($trigger, 'on_change_to', 'Missing on_change_to in trigger.');
        Assertion::isArray($trigger['on_change_to'], 'on_change_to must be an object');
        Assertion::count($trigger, 1, 'No extra keys allowed.');
        $onChangeTo = $trigger['on_change_to'];
        $this->validateFieldName($channel, $onChangeTo);
        if (array_intersect_key($onChangeTo, array_flip(['lt', 'le', 'gt', 'ge']))) {
            $this->validateThresholdTrigger($channel, $onChangeTo);
        } elseif (array_intersect_key($onChangeTo, array_flip(['eq', 'ne']))) {
            $this->validateEqualityTrigger($channel, $onChangeTo);
        } else {
            throw new \InvalidArgumentException('Unrecognized trigger format.');
        }
    }

    private function validateFieldName(IODeviceChannel $channel, array $onChangeTo): void {
        $possibleFieldNames = array_filter(self::FIELD_NAMES, function ($functionIds) use ($channel) {
            return in_array($channel->getFunction()->getId(), $functionIds);
        });
        if ($possibleFieldNames) {
            if (count($possibleFieldNames) > 1 || isset($onChangeTo['name'])) {
                Assertion::keyExists($onChangeTo, 'name', 'Missing trigger field definition.');
                $possibleFieldNames = array_keys($possibleFieldNames);
                Assertion::inArray($onChangeTo['name'], $possibleFieldNames, 'Unsupported field name.');
            }
        } else {
            Assertion::keyNotExists($onChangeTo, 'name', 'Field name is not required for this channel. Remove it.');
        }
    }

    /**
     * @OA\Schema(schema="ReactionTriggerLt", description="Reaction trigger based on numeric state (less than).",
     *     @OA\Property(property="lt", type="number"),
     *     @OA\Property(property="name", ref="#/components/schemas/ReactionTriggerFieldNames"),
     *     @OA\Property(property="resume", @OA\Property(property="ge", type="number"))
     * )
     * @OA\Schema(schema="ReactionTriggerLe", description="Reaction trigger based on numeric state (less than or equal).",
     *     @OA\Property(property="le", type="number"),
     *     @OA\Property(property="name", ref="#/components/schemas/ReactionTriggerFieldNames"),
     *     @OA\Property(property="resume", @OA\Property(property="gt", type="number"))
     * )
     * @OA\Schema(schema="ReactionTriggerGt", description="Reaction trigger based on numeric state (greater than).",
     *     @OA\Property(property="gt", type="number"),
     *     @OA\Property(property="name", ref="#/components/schemas/ReactionTriggerFieldNames"),
     *     @OA\Property(property="resume", @OA\Property(property="le", type="number"))
     * )
     * @OA\Schema(schema="ReactionTriggerGe", description="Reaction trigger based on numeric state (greater than or equal).",
     *     @OA\Property(property="ge", type="number"),
     *     @OA\Property(property="name", ref="#/components/schemas/ReactionTriggerFieldNames"),
     *     @OA\Property(property="resume", @OA\Property(property="lt", type="number"))
     * )
     */
    private function validateThresholdTrigger(IODeviceChannel $channel, array $onChangeTo): void {
        Assertion::inArray($channel->getFunction()->getId(), self::THRESHOLD_SUPPORT, 'Threshold trigger unsupported for this function.');
        $mainCondition = array_intersect_key($onChangeTo, array_flip(['lt', 'le', 'gt', 'ge']));
        Assertion::count($mainCondition, 1, 'You must define only one condition for the threshold (lt, le, gt or ge).');
        $operator = key($mainCondition);
        Assertion::numeric($onChangeTo[$operator], 'Threshold must be numeric.');
        if (isset($onChangeTo['resume'])) {
            $resume = $onChangeTo['resume'];
            $resumeOperator = ['lt' => 'ge', 'le' => 'gt', 'ge' => 'lt', 'gt' => 'le'][$operator];
            Assertion::isArray($resume, 'Invalid resume');
            Assertion::count($resume, 1, 'Invalid resume');
            Assertion::keyExists($resume, $resumeOperator, "Resume for this trigger must have a $resumeOperator operator.");
            Assertion::numeric($resume[$resumeOperator], 'Threshold must be numeric.');
            if (in_array($operator, ['lt', 'le'])) {
                Assertion::lessOrEqualThan(
                    $onChangeTo[$operator],
                    $resume[$resumeOperator],
                    'Resume value must be greater than monitored value.'
                );
            } else {
                Assertion::greaterOrEqualThan(
                    $onChangeTo[$operator],
                    $resume[$resumeOperator],
                    'Resume value must be less than monitored value.'
                );
            }
        }
    }

    /**
     * @OA\Schema(schema="ReactionTriggerEq", description="Reaction trigger based on numeric or binary state (equal).",
     *     @OA\Property(property="eq", oneOf={
     *       @OA\Schema(type="number"),
     *       @OA\Schema(type="string", enum={"hi", "closed", "on", "lo", "low", "open", "off"}),
     *     }),
     *     @OA\Property(property="name", ref="#/components/schemas/ReactionTriggerFieldNames"),
     * )
     * @OA\Schema(schema="ReactionTriggerNe", description="Reaction trigger based on numeric or binary state (not equal).",
     *     @OA\Property(property="ne", oneOf={
     *       @OA\Schema(type="number"),
     *       @OA\Schema(type="string", enum={"hi", "closed", "on", "lo", "low", "open", "off"}),
     *     }),
     *     @OA\Property(property="name", ref="#/components/schemas/ReactionTriggerFieldNames"),
     * )
     */
    private function validateEqualityTrigger(IODeviceChannel $channel, array $onChangeTo): void {
        $mainCondition = array_intersect_key($onChangeTo, array_flip(['eq', 'ne']));
        Assertion::count($mainCondition, 1, 'You must define only one condition for the threshold (eq, ne).');
        $operator = key($mainCondition);
        if (in_array($channel->getFunction()->getId(), self::THRESHOLD_SUPPORT)) {
            Assertion::numeric($onChangeTo[$operator], 'Threshold must be numeric.');
        } else {
            Assertion::inArray($onChangeTo[$operator], ['hi', 'closed', 'on', 'lo', 'low', 'open', 'off'], 'Invalid comparison value.');
        }
    }
}
