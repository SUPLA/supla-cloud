<?php

namespace SuplaBundle\Auth;

use Doctrine\ORM\EntityManagerInterface;
use FOS\OAuthServerBundle\Entity\AccessTokenManager;
use FOS\OAuthServerBundle\Model\TokenInterface;
use SuplaBundle\Entity\Main\OAuth\AccessToken;
use Symfony\Component\HttpFoundation\RequestStack;

class SuplaAccessTokenManager extends AccessTokenManager {
    /** @var RequestStack */
    private $requestStack;

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $em) {
        parent::__construct($em, AccessToken::class);
        $this->entityManager = $em;
    }

    /** @required */
    public function setRequestStack(RequestStack $requestStack): void {
        $this->requestStack = $requestStack;
    }

    public function createToken(): TokenInterface {
        return new AccessToken($this->requestStack->getCurrentRequest());
    }

    /**
     * The default FOS implementation deletes everything where
     * `expiresAt < time()`. Two scenarios produce non-expiring tokens we MUST
     * preserve:
     *   - legacy rows with `expires_at IS NULL` (created before klapaudius
     *     migration; SQL excludes them from `< time()` automatically),
     *   - fresh personal tokens created via createPersonalAccessToken() —
     *     AccessToken's constructor initialises the typed `int $expiresAt`
     *     property to 0 (it has to, otherwise persist fails on uninitialised
     *     typed property), so they end up in the database with 0. Without the
     *     `expiresAt > 0` guard, `0 < time()` is true and the hourly cleanup
     *     wipes them.
     */
    public function deleteExpired(): int {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->delete()
            ->from(AccessToken::class, 'at')
            ->where('at.expiresAt > 0')
            ->andWhere('at.expiresAt < :time')
            ->setParameter('time', time());
        return (int) $qb->getQuery()->execute();
    }
}
