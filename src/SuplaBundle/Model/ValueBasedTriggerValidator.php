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
 *       @OA\Schema(ref="#/components/schemas/ReactionTriggerThresholdLt"),
 *       @OA\Schema(ref="#/components/schemas/ReactionTriggerThresholdLe"),
 *       @OA\Schema(ref="#/components/schemas/ReactionTriggerThresholdGt"),
 *       @OA\Schema(ref="#/components/schemas/ReactionTriggerThresholdGe"),
 *     }
 *   )
 * )
 */
class ValueBasedTriggerValidator {
    private const FIELD_NAMES = [
        'temperature' => [ChannelFunction::THERMOMETER, ChannelFunction::HUMIDITYANDTEMPERATURE],
        'humidity' => [ChannelFunction::HUMIDITY, ChannelFunction::HUMIDITYANDTEMPERATURE],
    ];

    private const THRESHOLD_SUPPORT = [
        ChannelFunction::THERMOMETER,
        ChannelFunction::HUMIDITYANDTEMPERATURE,
        ChannelFunction::HUMIDITY,
        ChannelFunction::DEPTHSENSOR,
    ];

    public function validate(IODeviceChannel $channel, array $trigger): void {
        Assertion::keyExists($trigger, 'on_change_to', 'Missing on_change_to in trigger.');
        Assertion::isArray($trigger['on_change_to'], 'on_change_to must be an object');
        Assertion::count($trigger, 1, 'No extra keys allowed.');
        $onChangeTo = $trigger['on_change_to'];
        if (array_intersect_key($onChangeTo, array_flip(['lt', 'le', 'gt', 'ge']))) {
            $this->validateThresholdTrigger($channel, $onChangeTo);
        } else {
            throw new \InvalidArgumentException('Unrecognized trigger format.');
        }
    }

    /**
     * @OA\Schema(schema="ReactionTriggerFieldNames", type="string", example="temperature", enum={"temperature", "humidity"})
     * @OA\Schema(schema="ReactionTriggerThresholdLt", description="Reaction trigger based on numeric state (less than).",
     *     @OA\Property(property="lt", type="number"),
     *     @OA\Property(property="name", ref="#/components/schemas/ReactionTriggerFieldNames"),
     *     @OA\Property(property="resume", @OA\Property(property="ge", type="number"))
     * )
     * @OA\Schema(schema="ReactionTriggerThresholdLe", description="Reaction trigger based on numeric state (less than or equal).",
     *     @OA\Property(property="le", type="number"),
     *     @OA\Property(property="name", ref="#/components/schemas/ReactionTriggerFieldNames"),
     *     @OA\Property(property="resume", @OA\Property(property="gt", type="number"))
     * )
     * @OA\Schema(schema="ReactionTriggerThresholdGt", description="Reaction trigger based on numeric state (greater than).",
     *     @OA\Property(property="gt", type="number"),
     *     @OA\Property(property="name", ref="#/components/schemas/ReactionTriggerFieldNames"),
     *     @OA\Property(property="resume", @OA\Property(property="le", type="number"))
     * )
     * @OA\Schema(schema="ReactionTriggerThresholdGe", description="Reaction trigger based on numeric state (greater than or equal).",
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
                Assertion::lessOrEqualThan($onChangeTo[$operator], $resume[$resumeOperator], 'Resume value must be greater than monitored value.');
            } else {
                Assertion::greaterOrEqualThan($onChangeTo[$operator], $resume[$resumeOperator], 'Resume value must be less than monitored value.');
            }
        }
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
}
