<?php

namespace SuplaBundle\Message;

use Assert\Assertion;
use SuplaBundle\Repository\UserRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Twig\Environment;

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
        $templatePath = "SuplaBundle::Email/$data[userLocale]/{$email->getTemplateName()}";
        $textRendered = $this->twig->render($templatePath . '.txt.twig', $data);
        [$subject, $text] = explode(self::SUBJECT_DELIMITER, $textRendered);
        $html = $this->twig->render($templatePath . '.html.twig', $data);
        $this->messageBus->dispatch(new EmailMessage($data['userEmail'], $subject, $text, $html));
    }
}
