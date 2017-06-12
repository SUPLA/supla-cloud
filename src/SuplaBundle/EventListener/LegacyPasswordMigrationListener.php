<?php
namespace SuplaBundle\EventListener;

use Doctrine\ORM\EntityManager;
use SuplaBundle\Entity\User;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LegacyPasswordMigrationListener {
    private $encoderFactory;
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EncoderFactoryInterface $encoderFactory, EntityManager $entityManager) {
        $this->encoderFactory = $encoderFactory;
        $this->entityManager = $entityManager;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event) {
        /** @var User $user */
        $user = $event->getAuthenticationToken()->getUser();
        $token = $event->getAuthenticationToken();

        if ($user->hasLegacyPassword()) {
            $plainPassword = $token->getCredentials();
            $user->clearLegacyPassword();
            $encoder = $this->encoderFactory->getEncoder($user);
            $user->setPassword($encoder->encodePassword($plainPassword, $user->getSalt()));
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
        $token->eraseCredentials();
    }
}
