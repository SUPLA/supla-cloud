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
        $templatePath = "SuplaBundle::Email/pl/{$email->getTemplateName()}";
        $textRendered = $this->twig->render($templatePath . '.txt.twig', $email->getData());
        [$subject, $text] = explode(self::SUBJECT_DELIMITER, $textRendered);
        $html = $this->twig->render($templatePath . '.html.twig', $email->getData());
        $this->messageBus->dispatch(new EmailMessage($user->getEmail(), $subject, $text, $html));
    }
}
