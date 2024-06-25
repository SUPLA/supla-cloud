<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assertion;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Exception\ApiException;
use SuplaBundle\Supla\SuplaOcrClient;

class OcrSettingsUserConfigTranslator extends UserConfigTranslator {
    use FixedRangeParamsTranslator;

    private SuplaOcrClient $ocr;

    public function __construct(SuplaOcrClient $ocr) {
        $this->ocr = $ocr;
    }

    public function getConfig(HasUserConfig $subject): array {
        return [
            'ocrSettings' => $subject->getUserConfigValue('ocrSettings', new \stdClass()),
        ];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('ocrSettings', $config)) {
            $config = $config['ocrSettings'];
            Assertion::isArray($config);
            try {
                $this->ocr->updateSettings($subject, $config);
                $subject->setUserConfigValue('ocrSettings', $config);
            } catch (ApiException $e) {
                Assertion::true(false, 'Cannot update OCR settings. Try again in a while.'); // i18n
            }
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
