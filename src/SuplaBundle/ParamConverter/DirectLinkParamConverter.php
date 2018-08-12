<?php
namespace SuplaBundle\ParamConverter;

use Assert\Assertion;
use SuplaBundle\Entity\DirectLink;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Repository\IODeviceChannelRepository;

class DirectLinkParamConverter extends AbstractBodyParamConverter {
    use CurrentUserAware;

    /** @var IODeviceChannelRepository */
    private $channelRepository;

    public function __construct(IODeviceChannelRepository $channelRepository) {
        $this->channelRepository = $channelRepository;
    }

    public function getConvertedClass(): string {
        return DirectLink::class;
    }

    public function convert(array $data) {
        $user = $this->getCurrentUserOrThrow();
        Assertion::keyExists($data, 'channelId', 'ChannelId must be set.');
        Assertion::keyExists($data, 'allowedActions', 'AllowedActions must be set.');
        Assertion::isArray($data['allowedActions'], 'AllowedActions must be an array.');
        $channel = $this->channelRepository->findForUser($user, $data['channelId']);
        $link = new DirectLink($channel);
        $link->setCaption($data['caption'] ?? '');
        $possibleActions = EntityUtils::mapToIds($channel->getFunction()->getPossibleActions());
        $possibleActions[] = ChannelFunctionAction::READ;
        $allowedActions = array_map(function ($allowedActionName) use ($possibleActions) {
            $allowedAction = ChannelFunctionAction::fromString($allowedActionName);
            Assertion::inArray($allowedAction->getId(), $possibleActions, "Action $allowedActionName cannot be executed on this channel.");
            return $allowedAction;
        }, $data['allowedActions']);
        $link->setAllowedActions($allowedActions);
        return $link;
    }
}
