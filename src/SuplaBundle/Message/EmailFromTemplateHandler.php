<?php

namespace SuplaBundle\Message;

use SuplaBundle\Repository\UserRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Twig\Environment;
use Twig\Error\LoaderError;

class EmailFromTemplateHandler implements MessageHandlerInterface {
    private const SUBJECT_DELIMITER = '#### SUBJECT-DELIMITER ####';

    /** @var Environment */
    private $twig;
    /** @var UserRepository */
    private $userRepository;
    /** @var MessageBusInterface */
    private $messageBus;
    /** @var string */
    private $defaultLocale;

    public function __construct(MessageBusInterface $messageBus, Environment $twig, UserRepository $userRepository, string $defaultLocale) {
        $this->messageBus = $messageBus;
        $this->twig = $twig;
        $this->userRepository = $userRepository;
        $this->defaultLocale = $defaultLocale;
    }

    public function __invoke(EmailFromTemplate $email) {
        if ($email->getUserId()) {
            $user = $this->userRepository->find($email->getUserId());
            $userLocale = $user->getLocale() ?: $this->defaultLocale;
            $data = [
                'userId' => $user->getId(),
                'userEmail' => $user->getEmail(),
                'userLocale' => $userLocale,
            ];
        } elseif ($email->getRecipient()) {
            $data = [
                'userEmail' => $email->getRecipient(),
                'userLocale' => $this->defaultLocale,
            ];
        } else {
            throw new \InvalidArgumentException('No userId and no recipient given.');
        }
        $data = array_merge($email->getData(), $data);
//        $locale = $this->localeForTemplate($data['userLocale'], $email->getTemplateName(), $data);
        $textRendered = $this->render($email->getTemplateName() . '.txt', $data);
        [$subject, $text] = explode(self::SUBJECT_DELIMITER, $textRendered);
        $html = $this->render($email->getTemplateName() . '.html', $data);
        $this->messageBus->dispatch(new EmailMessage($data['userEmail'], trim($subject), $text, $html));
    }

    private function localeForTemplate(string $userLocale, string $templateName, array $data) {
        foreach ([$userLocale, $this->defaultLocale, 'en'] as $possibleLocale) {
            $path = "SuplaBundle::Email/$possibleLocale/$templateName.txt.twig";
            try {
                $this->twig->render($path, $data);
                return $possibleLocale;
            } catch (LoaderError $e) {
            }
        }
        throw new \InvalidArgumentException('Invalid email template: ' . $templateName);
    }

    private function render(string $templateName, array $data): ?string {
        $path = "SuplaBundle::Email/$templateName.twig";
        try {
            return $this->twig->render($path, $data);
        } catch (LoaderError $e) {
            return null;
        }
    }
}
