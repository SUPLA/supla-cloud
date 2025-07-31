<?php

namespace SuplaBundle\Message;

use Assert\Assertion;
use SuplaBundle\Entity\Main\AccessID;
use SuplaBundle\Entity\Main\PushNotification;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Repository\AccessIdRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

readonly class PushMessageHandler implements MessageHandlerInterface {
    public function __construct(private ChannelActionExecutor $channelActionExecutor, private AccessIdRepository $accessIdRepository) {
    }

    public function __invoke(PushMessage $message) {
        $this->send($message);
    }

    public function send(PushMessage $message): void {
        /** @var AccessID[] $aids */
        $aids = array_map(fn($aid) => $this->accessIdRepository->find($aid), $message->getAccessIdIds());
        $aids = array_filter($aids);
        $userIds = array_map(fn($aid) => $aid->getUser()->getId(), $aids);
        Assertion::count($userIds, 1, 'All accessIds must belong to the same user.');
        $pushNotification = new PushNotification($aids[0]->getUser());
        $params = [
            'title' => $message->getTitle(),
            'body' => $message->getBody(),
            'accessIds' => array_values(array_map(fn($aid) => $aid->getId(), $aids)),
        ];
        $this->channelActionExecutor->executeAction($pushNotification, ChannelFunctionAction::SEND(), $params);
    }
}
