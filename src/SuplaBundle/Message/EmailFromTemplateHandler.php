<?php

namespace SuplaBundle\Message;

use SuplaBundle\Model\TimeProvider;
use SuplaBundle\Repository\UserRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Extra\Intl\IntlExtension;

class EmailFromTemplateHandler implements MessageHandlerInterface {
    private const SUBJECT_DELIMITER = '#### SUBJECT-DELIMITER ####';

    /** @var Environment */
    private $twig;
    /** @var UserRepository */
    private $userRepository;
    /** @var MessageBusInterface */
    private $messageBus;
    /** @var TimeProvider */
    private $timeProvider;
    /** @var string */
    private $defaultLocale;

    public function __construct(
        MessageBusInterface $messageBus,
        Environment $twig,
        UserRepository $userRepository,
        TimeProvider $timeProvider,
        string $defaultLocale
    ) {
        $this->messageBus = $messageBus;
        $this->twig = $twig;
        $twig->addExtension(new IntlExtension());
        $this->userRepository = $userRepository;
        $this->timeProvider = $timeProvider;
        $this->defaultLocale = $defaultLocale;
    }

    public function __invoke(EmailFromTemplate $email) {
        if ($email->isBurnt($this->timeProvider)) {
            return;
        }
        $locale = (($email->getData() ?: [])['locale'] ?? null) ?: $this->defaultLocale;
        if ($email->getUserId()) {
            $user = $this->userRepository->find($email->getUserId());
            if (!$user || in_array($email->getTemplateName(), $user->getPreference('optOutNotifications', []))) {
                return;
            }
            $userLocale = $user->getLocale() ?: $locale;
            $data = [
                'userId' => $user->getId(),
                'userEmail' => $user->getEmail(),
                'userLocale' => $userLocale,
            ];
        } elseif ($email->getRecipient()) {
            $data = [
                'userEmail' => $email->getRecipient(),
                'userLocale' => $locale,
            ];
        } else {
            throw new \InvalidArgumentException('No userId and no recipient given.');
        }
        $data = array_merge($email->getData(), $data);
        $data['templateName'] = $email->getTemplateName();
        $data['eventTimestamp'] = $email->getEventTimestamp();
        $timezone = new \DateTimeZone(isset($user) ? $user->getTimezone() : 'UTC');
        $data['eventTime'] = new \DateTimeImmutable('@' . $email->getEventTimestamp(), $timezone);
        $textRendered = $this->render($email->getTemplateName() . '.txt', $data);
        [$subject, $text] = explode(self::SUBJECT_DELIMITER, $textRendered);
        $html = $this->render($email->getTemplateName() . '.html', $data);
        $this->messageBus->dispatch(new EmailMessage($data['userEmail'], trim($subject), trim($text), $html));
    }

    private function render(string $templateName, array $data): ?string {
        $path = "@Supla/Email/$templateName.twig";
        try {
            return $this->twig->render($path, $data);
        } catch (LoaderError $e) {
            return null;
        }
    }
}
