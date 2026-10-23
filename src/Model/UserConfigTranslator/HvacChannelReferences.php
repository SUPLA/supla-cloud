<?php

namespace App\Model\UserConfigTranslator;

/** Canonical HVAC reference names; legacy conversion belongs exclusively to DB migrations. */
final class HvacChannelReferences {
    public const FIELDS = [
        'mainThermometer', 'auxThermometer', 'binarySensor',
        'masterThermostat', 'pumpSwitch', 'heatOrColdSourceSwitch',
    ];
}
