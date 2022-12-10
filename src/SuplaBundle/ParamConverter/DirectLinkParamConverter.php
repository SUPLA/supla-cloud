<?php
namespace SuplaBundle\ParamConverter;

use Assert\Assertion;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\DirectLink;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Repository\ActionableSubjectRepository;

class DirectLinkParamConverter extends AbstractBodyParamConverter {
    use CurrentUserAware;

    /** @var ActionableSubjectRepository */
    private $subjectRepository;

    public function __construct(ActionableSubjectRepository $subjectRepository) {
        $this->subjectRepository = $subjectRepository;
    }

    public function getConvertedClass(): string {
        return DirectLink::class;
    }

    public function convert(array $data) {
        $user = $this->getCurrentUserOrThrow();
        Assertion::keyExists($data, 'subjectId', 'You must set subjectId for the direct link.');
        Assertion::keyExists($data, 'subjectType', 'You must set subjectType for the direct link.');
        if (!isset($data['allowedActions'])) {
            $data['allowedActions'] = [];
        }
        Assertion::isArray($data['allowedActions'], 'AllowedActions must be an array.');
        $subject = $this->subjectRepository->findForUser($user, $data['subjectType'], $data['subjectId']);
        Assertion::notInArray(
            $subject->getFunction()->getId(),
            [ChannelFunction::NONE, ChannelFunction::UNSUPPORTED, ChannelFunction::ACTION_TRIGGER],
            'Cannot create direct links for this function.'
        );
        $link = new DirectLink($subject);
        $link->setCaption($data['caption'] ?? '');
        Assertion::maxLength($link->getCaption(), 100, 'Caption is too long.'); // i18n
        $link->setEnabled($data['enabled'] ?? false);
        $link->setDisableHttpGet($data['disableHttpGet'] ?? false);
        $possibleActions = EntityUtils::mapToIds($subject->getPossibleActions());
        $possibleActions[] = ChannelFunctionAction::READ;
        $allowedActions = array_map(function ($allowedActionName) use ($possibleActions) {
            $allowedAction = ChannelFunctionAction::fromString($allowedActionName);
            Assertion::inArray($allowedAction->getId(), $possibleActions, "Action $allowedActionName cannot be executed on this channel.");
            return $allowedAction;
        }, $data['allowedActions']);
        $link->setAllowedActions($allowedActions);
        if (isset($data['activeDateRange'])) {
            Assertion::isArray($data['activeDateRange'], 'activeDateRange must be an array');
            $activeFrom = $data['activeDateRange']['dateStart'] ?? null;
            if ($activeFrom) {
                $link->setActiveFrom(new \DateTime($activeFrom));
            }
            $activeTo = $data['activeDateRange']['dateEnd'] ?? null;
            if ($activeTo) {
                $link->setActiveTo(new \DateTime($activeTo));
            }
        }
        $link->setExecutionsLimit(isset($data['executionsLimit']) && is_numeric($data['executionsLimit'])
            ? $data['executionsLimit'] : null);
        return $link;
    }
}
