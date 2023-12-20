<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Entity\Main\GateClosingRule;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Repository\GateClosingRuleRepository;
use SuplaBundle\Utils\JsonArrayObject;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GateClosingRuleParamTranslator extends UserConfigTranslator {
    use FixedRangeParamsTranslator;

    private const MIN_TIME = 300;

    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var GateClosingRuleRepository */
    private $repository;
    /** @var NormalizerInterface */
    private $normalizer;

    public function __construct(EntityManagerInterface $entityManager, NormalizerInterface $normalizer) {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(GateClosingRule::class);
        $this->normalizer = $normalizer;
    }

    public function getConfig(HasUserConfig $subject): array {
        $rule = $this->repository->find($subject->getId());
        $closingRuleConfig = $rule ? $this->normalizer->normalize($rule) : new JsonArrayObject([]);
        return ['closingRule' => $closingRuleConfig];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        $ruleConfig = $config['closingRule'] ?? [];
        if ($ruleConfig instanceof JsonArrayObject) {
            $ruleConfig = $ruleConfig->toArray();
        }
        if ($ruleConfig) {
            /** @var GateClosingRule $rule */
            $rule = $this->repository->find($subject->getId());
            if (!$rule) {
                $rule = new GateClosingRule($subject);
            }
            if (array_key_exists('enabled', $ruleConfig)) {
                $rule->setEnabled(boolval($ruleConfig['enabled']));
            }
            if (array_key_exists('maxTimeOpen', $ruleConfig)) {
                $rule->setMaxTimeOpen(intval($this->getValueInRange($ruleConfig['maxTimeOpen'], self::MIN_TIME, 3600 * 8)));
            }
            if (array_key_exists('activeHours', $ruleConfig)) {
                if ($ruleConfig['activeHours']) {
                    Assertion::isArray($ruleConfig['activeHours']);
                    $rule->setActiveHours($ruleConfig['activeHours']);
                } else {
                    $rule->setActiveHours(null);
                }
            }
            if (array_key_exists('activeFrom', $ruleConfig)) {
                if ($ruleConfig['activeFrom']) {
                    Assertion::date($ruleConfig['activeFrom'], \DateTime::ATOM);
                    $rule->setActiveFrom(new \DateTime($ruleConfig['activeFrom']));
                } else {
                    $rule->setActiveFrom(null);
                }
            }
            if (array_key_exists('activeTo', $ruleConfig)) {
                if ($ruleConfig['activeTo']) {
                    Assertion::date($ruleConfig['activeTo'], \DateTime::ATOM);
                    $rule->setActiveTo(new \DateTime($ruleConfig['activeTo']));
                } else {
                    $rule->setActiveFrom(null);
                }
            }
            if ($rule->getActiveFrom() && $rule->getActiveTo()) {
                Assertion::lessThan($rule->getActiveFrom()->getTimestamp(), $rule->getActiveTo()->getTimestamp());
            }
            if (!$rule->getMaxTimeOpen()) {
                $rule->setMaxTimeOpen(self::MIN_TIME);
            }
            $this->entityManager->persist($rule);
        }
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::CONTROLLINGTHEGATE,
            ChannelFunction::CONTROLLINGTHEGARAGEDOOR,
        ]);
    }
}
