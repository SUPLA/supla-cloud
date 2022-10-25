<?php

namespace SuplaBundle\Model\ChannelParamsTranslator;

use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\GateClosingRule;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Repository\GateClosingRuleRepository;
use SuplaBundle\Utils\JsonArrayObject;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GateClosingRuleParamTranslator implements ChannelParamTranslator {
    use FixedRangeParamsTranslator;

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

    public function getConfigFromParams(IODeviceChannel $channel): array {
        $rule = $this->repository->find($channel->getId());
        $closingRuleConfig = $rule ? $this->normalizer->normalize($rule) : new JsonArrayObject([]);
        return ['closingRule' => $closingRuleConfig];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        $ruleConfig = $config['closingRule'] ?? [];
        if ($ruleConfig) {
            /** @var GateClosingRule $rule */
            $rule = $this->repository->find($channel->getId());
            if (!$rule) {
                $rule = new GateClosingRule($channel);
            }
            if (array_key_exists('enabled', $ruleConfig)) {
                $rule->setEnabled(boolval($ruleConfig['enabled']));
            }
            if (array_key_exists('maxTimeOpen', $ruleConfig)) {
                $rule->setMaxTimeOpen(intval($this->getValueInRange($ruleConfig['maxTimeOpen'], 5, 3600)));
            }
            if (array_key_exists('activeHours', $ruleConfig)) {
                Assertion::isArray($ruleConfig['activeHours']);
                $rule->setActiveHours($ruleConfig['activeHours']);
            }
            if ($rule->getMaxTimeOpen()) {
                $this->entityManager->persist($rule);
            }
        }
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::CONTROLLINGTHEGATE,
            ChannelFunction::CONTROLLINGTHEGARAGEDOOR,
        ]);
    }
}
