<?php

namespace App\Model;

use App\Enums\InstanceSettings;
use App\Exception\ApiException;
use App\Repository\SettingsStringRepository;
use App\Supla\SuplaAutodiscover;

readonly class TargetCloudAuthTokenRotator {
    private const MIN_ROTATION_DELAY = 432000;
    private const MAX_ROTATION_DELAY = 864000;

    public function __construct(
        private SuplaAutodiscover $autodiscover,
        private SettingsStringRepository $settings,
        private TimeProvider $timeProvider
    ) {
    }

    public function isDue(): bool {
        if (!$this->autodiscover->isTarget()) {
            return false;
        }
        if ($this->pendingToken()) {
            return true;
        }
        $nextRotationAt = $this->settingValue(InstanceSettings::TARGET_TOKEN_ROTATION_NEXT_AT);
        if (!$nextRotationAt) {
            $this->scheduleNextRotation();
            return false;
        }
        return (int)$nextRotationAt <= $this->timeProvider->getTimestamp();
    }

    public function rotate(bool $force = false): bool {
        if (!$this->autodiscover->isTarget()) {
            return false;
        }
        if (!$force && !$this->isDue()) {
            return false;
        }
        if (!$this->pendingToken()) {
            $this->createPendingRotation();
        }

        $pendingToken = $this->pendingToken();
        if ($pendingToken === $this->settings->getValue(InstanceSettings::TARGET_TOKEN)) {
            $this->verifyAndFinish();
            return true;
        }

        $response = $this->autodiscover->rotateTargetCloudAuthToken(
            $pendingToken,
            $this->settingValue(InstanceSettings::TARGET_TOKEN_ROTATION_PENDING_IDEMPOTENCY_KEY)
        );
        if (empty($response['previousTokenValidUntil'])) {
            throw new ApiException('Autodiscover did not confirm the target Cloud token rotation.');
        }
        $this->settings->setValue(InstanceSettings::TARGET_TOKEN, $pendingToken);
        $this->verifyAndFinish();
        return true;
    }

    private function createPendingRotation(): void {
        $currentToken = $this->settings->getValue(InstanceSettings::TARGET_TOKEN);
        $separatorPosition = strpos($currentToken, '_');
        if ($separatorPosition === false || !ctype_digit(substr($currentToken, 0, $separatorPosition))) {
            throw new ApiException('Invalid target Cloud token.');
        }
        $targetCloudId = substr($currentToken, 0, $separatorPosition);
        $this->settings->setValue(
            InstanceSettings::TARGET_TOKEN_ROTATION_PENDING_TOKEN,
            $targetCloudId . '_' . bin2hex(random_bytes(32))
        );
        $this->settings->setValue(InstanceSettings::TARGET_TOKEN_ROTATION_PENDING_IDEMPOTENCY_KEY, bin2hex(random_bytes(32)));
    }

    private function verifyAndFinish(): void {
        $this->autodiscover->verifyTargetCloudAuthToken();
        $this->scheduleNextRotation();
        $this->settings->clearValue(InstanceSettings::TARGET_TOKEN_ROTATION_PENDING_TOKEN);
        $this->settings->clearValue(InstanceSettings::TARGET_TOKEN_ROTATION_PENDING_IDEMPOTENCY_KEY);
    }

    private function scheduleNextRotation(): void {
        $this->settings->setValue(
            InstanceSettings::TARGET_TOKEN_ROTATION_NEXT_AT,
            (string)($this->timeProvider->getTimestamp() + random_int(self::MIN_ROTATION_DELAY, self::MAX_ROTATION_DELAY))
        );
    }

    private function pendingToken(): ?string {
        return $this->settingValue(InstanceSettings::TARGET_TOKEN_ROTATION_PENDING_TOKEN) ?: null;
    }

    private function settingValue(InstanceSettings $setting): ?string {
        return $this->settings->hasValue($setting) ? $this->settings->getValue($setting) : null;
    }
}
