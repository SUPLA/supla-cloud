<?php
namespace SuplaBundle\ParamConverter;

use Assert\Assertion;
use SuplaBundle\Entity\DirectLink;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\HasFunction;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Repository\ChannelGroupRepository;
use SuplaBundle\Repository\IODeviceChannelRepository;

class DirectLinkParamConverter extends AbstractBodyParamConverter {
    use CurrentUserAware;

    /** @var IODeviceChannelRepository */
    private $channelRepository;
    /** @var ChannelGroupRepository */
    private $channelGroupRepository;

    public function __construct(IODeviceChannelRepository $channelRepository, ChannelGroupRepository $channelGroupRepository) {
        $this->channelRepository = $channelRepository;
        $this->channelGroupRepository = $channelGroupRepository;
    }

    public function getConvertedClass(): string {
        return DirectLink::class;
    }

    public function convert(array $data) {
        $user = $this->getCurrentUserOrThrow();
        Assertion::keyExists($data, 'subjectId', 'You must set subjectId for the direct link.');
        Assertion::keyExists($data, 'subjectType', 'You must set subjectType for the direct link.');
        Assertion::inArray($data['subjectType'], ActionableSubjectType::toArray(), 'Invalid subject type.');
        if (!isset($data['allowedActions'])) {
            $data['allowedActions'] = [];
        }
        Assertion::isArray($data['allowedActions'], 'AllowedActions must be an array.');
        /** @var HasFunction $subject */
        $subject = $data['subjectType'] === ActionableSubjectType::CHANNEL
            ? $this->channelRepository->findForUser($user, $data['subjectId'])
            : $this->channelGroupRepository->findForUser($user, $data['subjectId']);
        Assertion::notEq(ChannelFunction::NONE, $subject->getFunction()->getId(), 'Cannot create direct link for NONE channel function.');
        Assertion::notEq(
            ChannelFunction::UNSUPPORTED,
            $subject->getFunction()->getId(),
            'Cannot create direct link for UNSUPPORTED channel function.'
        );
        $link = new DirectLink($subject);
        $link->setCaption($data['caption'] ?? '');
        $link->setEnabled($data['enabled'] ?? false);
        $link->setDisableHttpGet($data['disableHttpGet'] ?? false);
        $possibleActions = EntityUtils::mapToIds($subject->getFunction()->getPossibleActions());
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
