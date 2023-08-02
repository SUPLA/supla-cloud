<?php
namespace SuplaBundle\Serialization\RequestFiller;

use Assert\Assertion;
use SuplaBundle\Entity\Main\ValueBasedTrigger;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Model\ValueBasedTriggerValidator;

class ValueBasedTriggerRequestFiller extends AbstractRequestFiller {
    use CurrentUserAware;

    /** @var SubjectActionFiller */
    private $subjectActionFiller;
    /** @var ValueBasedTriggerValidator */
    private $triggerValidator;

    public function __construct(SubjectActionFiller $subjectActionFiller, ValueBasedTriggerValidator $triggerValidator) {
        $this->subjectActionFiller = $subjectActionFiller;
        $this->triggerValidator = $triggerValidator;
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
        [$subject, $action, $actionParam] = $this->subjectActionFiller->getSubjectAndAction($data);
        $vbt->setSubject($subject);
        $vbt->setAction($action);
        $vbt->setActionParam($actionParam);
        Assertion::keyExists($data, 'trigger', 'Missing trigger.');
        Assertion::isArray($data['trigger'], 'Invalid trigger definition.');
        $this->triggerValidator->validate($vbt->getOwningChannel(), $data['trigger']);
        $vbt->setTrigger($data['trigger']);
        return $vbt;
    }
}
