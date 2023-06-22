<?php
namespace SuplaBundle\Serialization\RequestFiller;

use Assert\Assertion;
use SuplaBundle\Entity\Main\ValueBasedTrigger;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Model\CurrentUserAware;

class ValueBasedTriggerRequestFiller extends AbstractRequestFiller {
    use CurrentUserAware;

    /** @var SubjectActionFiller */
    private $subjectActionFiller;

    public function __construct(SubjectActionFiller $subjectActionFiller) {
        $this->subjectActionFiller = $subjectActionFiller;
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
        // TODO validate trigger
        $vbt->setTrigger($data['trigger']);
        return $vbt;
    }
}
