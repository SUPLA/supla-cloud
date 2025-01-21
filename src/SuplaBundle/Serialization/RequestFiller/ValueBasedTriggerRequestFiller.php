<?php
namespace SuplaBundle\Serialization\RequestFiller;

use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\Main\PushNotification;
use SuplaBundle\Entity\Main\ValueBasedTrigger;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Model\ValueBasedTriggerValidator;

class ValueBasedTriggerRequestFiller extends AbstractRequestFiller {
    use ActivityConditionsRequestFiller;

    public function __construct(
        private readonly SubjectActionFiller $subjectActionFiller,
        private readonly ValueBasedTriggerValidator $triggerValidator,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /** @param ValueBasedTrigger $vbt */
    public function fillFromData(array $data, $vbt = null) {
        Assertion::notNull($vbt);
        Assertion::keyExists($data, 'subjectType', 'Invalid subject type.');
        Assertion::inArray($data['subjectType'], ActionableSubjectType::toArray(), 'Invalid subject type.');
        Assertion::keyExists(
            $data,
            'actionId',
            'You must set an action for each reaction.' // i18n
        );
        if ($vbt->getSubject() instanceof PushNotification) {
            $this->entityManager->remove($vbt->getSubject());
        }
        [$subject, $action, $actionParam] = $this->subjectActionFiller->getSubjectAndAction($vbt->getUser(), $data);
        $vbt->setSubject($subject);
        if ($subject instanceof PushNotification) {
            $subject->setChannel($vbt->getOwningChannel());
        }
        $vbt->setAction($action);
        $vbt->setActionParam($actionParam);
        Assertion::keyExists($data, 'trigger', 'Missing trigger.');
        Assertion::isArray($data['trigger'], 'Invalid trigger definition.');
        $this->triggerValidator->validate($vbt->getOwningChannel(), $data['trigger']);
        $vbt->setTrigger($data['trigger']);
        if (isset($data['enabled'])) {
            $vbt->setEnabled(boolval($data['enabled']));
        }
        $this->fillActivityConditions($data, $vbt);
        return $vbt;
    }
}
