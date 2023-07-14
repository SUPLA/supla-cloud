<?php
namespace SuplaBundle\Serialization\RequestFiller;

use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\Main\PushNotification;
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
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        SubjectActionFiller $subjectActionFiller,
        ValueBasedTriggerValidator $triggerValidator,
        EntityManagerInterface $entityManager
    ) {
        $this->subjectActionFiller = $subjectActionFiller;
        $this->triggerValidator = $triggerValidator;
        $this->entityManager = $entityManager;
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
        [$subject, $action, $actionParam] = $this->subjectActionFiller->getSubjectAndAction($data);
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
        return $vbt;
    }

    private function clearOldNotifications(array $actionsConfig) {
        foreach ($actionsConfig as $action) {
            if ($action['subjectType'] === ActionableSubjectType::NOTIFICATION) {
                $notification = $this->entityManager->find(PushNotification::class, $action['subjectId'] ?? 0);
                if ($notification) {
                    $this->entityManager->remove($notification);
                }
            }
        }
    }
}
