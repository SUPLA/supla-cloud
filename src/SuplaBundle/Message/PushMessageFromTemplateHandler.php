<?php

namespace SuplaBundle\Message;

use Assert\Assertion;
use SuplaBundle\Entity\Main\AccessID;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\UserPreferences;
use SuplaBundle\Model\TimeProvider;
use SuplaBundle\Repository\AccessIdRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Extra\Intl\IntlExtension;

class PushMessageFromTemplateHandler implements MessageHandlerInterface {
    private const SUBJECT_DELIMITER = '#### SUBJECT-DELIMITER ####';

    public function __construct(
        private MessageBusInterface $messageBus,
        private Environment $twig,
        private AccessIdRepository $accessIdRepository,
        private TimeProvider $timeProvider
    ) {
        if (!$twig->hasExtension(IntlExtension::class)) {
            $twig->addExtension(new IntlExtension());
        }
    }

    public function __invoke(PushMessageFromTemplate $pushMessage) {
        if ($pushMessage->isBurnt($this->timeProvider)) {
            return;
        }
        if (!$pushMessage->getAccessIdIds()) {
            return;
        }
        /** @var AccessID[] $aids */
        $aids = array_map(fn($aid) => $this->accessIdRepository->find($aid), $pushMessage->getAccessIdIds());
        $aids = array_filter($aids);
        $userIds = array_map(fn($aid) => $aid->getUser()->getId(), $aids);
        Assertion::count($userIds, 1, 'All accessIds must belong to the same user.');
        $user = $aids[0]->getUser();
        if (in_array($pushMessage->getTemplateName(), $user->getPreference(UserPreferences::OPT_OUT_NOTIFICATIONS_PUSH, []))) {
            return;
        }
        $message = $this->renderPushMessage($user, $pushMessage);
        $this->messageBus->dispatch($message);
    }

    private function renderPushMessage(User $user, PushMessageFromTemplate $pushMessageFromTemplate): PushMessage {
        $data = $pushMessageFromTemplate->getData();
        $data = array_merge($data, [
            'locale' => $data['locale'] ?? $user->getLocale(),
            'userId' => $user->getId(),
            'userEmail' => $user->getEmail(),
            'userLocale' => $user->getLocale(),
            'templateName' => $pushMessageFromTemplate->getTemplateName(),
            'eventTimestamp' => $pushMessageFromTemplate->getEventTimestamp(),
        ]);
        $timezone = new \DateTimeZone($user->getTimezone());
        $data['eventTime'] = new \DateTimeImmutable('@' . $pushMessageFromTemplate->getEventTimestamp(), $timezone);
        $textRendered = $this->render($pushMessageFromTemplate->getTemplateName(), $data);
        [$title, $text] = explode(self::SUBJECT_DELIMITER, $textRendered);
        return new PushMessage($pushMessageFromTemplate->getAccessIdIds(), trim($title), trim($text));
    }

    private function render(string $templateName, array $data): ?string {
        $path = "@Supla/PushMessage/$templateName.twig";
        try {
            return $this->twig->render($path, $data);
        } catch (LoaderError $e) {
            return null;
        }
    }
}
