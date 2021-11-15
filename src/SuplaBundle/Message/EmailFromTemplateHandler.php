<?php

namespace SuplaBundle\Message;

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

    public function __construct(MessageBusInterface $messageBus, Environment $twig, UserRepository $userRepository) {
        $this->messageBus = $messageBus;
        $this->twig = $twig;
        $this->userRepository = $userRepository;
    }

    public function __invoke(EmailFromTemplate $email) {
        $user = $this->userRepository->find($email->getUserId());
        $userLocale = $user->getLocale() ?: 'pl';
        $data = array_merge($email->getData(), [
            'userId' => $user->getId(),
            'userEmail' => $user->getEmail(),
            'userLocale' => $userLocale,
        ]);
        $templatePath = "SuplaBundle::Email/$userLocale/{$email->getTemplateName()}";
        $textRendered = $this->twig->render($templatePath . '.txt.twig', $data);
        [$subject, $text] = explode(self::SUBJECT_DELIMITER, $textRendered);
        $html = $this->twig->render($templatePath . '.html.twig', $data);
        $this->messageBus->dispatch(new EmailMessage($user->getEmail(), $subject, $text, $html));
    }
}
