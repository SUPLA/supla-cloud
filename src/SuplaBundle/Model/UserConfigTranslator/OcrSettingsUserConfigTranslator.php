<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assert;
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
        $ocrProp = $subject->getProperty('ocr');
        if ($ocrProp) {
            $ocrConfig = $subject->getUserConfigValue('ocr', []);
            $ocrConfig['availableLightingModes'] = $ocrProp['availableLightingModes'] ?? [];
            return ['ocr' => $ocrConfig];
        } else {
            return [];
        }
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('ocr', $config)) {
            $ocrConfig = $config['ocr'];
            Assertion::isArray($ocrConfig);
            if (array_key_exists('photoIntervalSec', $ocrConfig)) {
                Assert::that($ocrConfig['photoIntervalSec'], null, 'ocr.photoIntervalSec')->integer()->between(5, 300);
            }
            if (array_key_exists('lightingMode', $ocrConfig)) {
                Assert::that($ocrConfig['lightingMode'], null, 'ocr.lightingMode')
                    ->string()
                    ->inArray($this->getConfig($subject)['ocr']['availableLightingModes']);
            }
            if (array_key_exists('lightingLevel', $ocrConfig)) {
                Assert::that($ocrConfig['lightingLevel'], null, 'ocr.lightingLevel')->integer()->between(1, 100);
            }
            if (array_key_exists('photoSettings', $ocrConfig)) {
                try {
                    $this->ocr->updateSettings($subject, $ocrConfig['photoSettings']);
                } catch (ApiException $e) {
                    Assertion::true(false, 'Cannot update OCR settings. Try again in a while.'); // i18n
                }
            }
            $subject->setUserConfigValue('ocr', $ocrConfig);
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
