<?php

namespace SuplaBundle\Message;

use SuplaBundle\Repository\UserRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Twig\Environment;

class EmailFromTemplateHandler implements MessageHandlerInterface {
    /** @var Environment */
    private $twig;
    /** @var UserRepository */
    private $userRepository;

    public function __construct(Environment $twig, UserRepository $userRepository) {
        $this->twig = $twig;
        $this->userRepository = $userRepository;
    }

    public function __invoke(EmailFromTemplate $email) {
        $user = $this->userRepository->find($email->getUserId());
        $templatePath = "SuplaBundle::Email/pl/{$email->getTemplate()}.txt.twig";
        $text = $this->twig->render($templatePath, $email->getData());

        echo $text;
    }
}
