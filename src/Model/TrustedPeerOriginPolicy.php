<?php

namespace App\Model;

/** Controls strict validation of configured/autodiscovered peer origins. */
final class TrustedPeerOriginPolicy {
    public static function isValidationEnabled(): bool {
        $value = getenv('SUPLA_TRUSTED_CLOUD_ORIGIN_VALIDATION');
        return $value === false || filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
