<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.
 
 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace SuplaBundle\Model;

use Doctrine\Common\Persistence\ManagerRegistry;
use SuplaBundle\Entity\Location;
use SuplaBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Translation\TranslatorInterface;

class LocationManager {
    protected $translator;
    protected $doctrine;
    protected $rep;
    protected $sec;

    public function __construct(TranslatorInterface $translator, ManagerRegistry $doctrine, TokenStorage $security_token) {
        $this->translator = $translator;
        $this->doctrine = $doctrine;
        $this->rep = $doctrine->getRepository('SuplaBundle:Location');
        $this->sec = $security_token;
    }

    public function anyLocationExists(User $user) {
        return $this->rep->findOneBy(['user' => $user]) !== null;
    }

    public function totalCount(User $user) {
        $qb = $this->rep->createQueryBuilder('l');

        return intval($qb->select('count(l.id)')
            ->where($qb->expr()->eq('l.user', ':user'))
            ->setParameter('user', $user->getId())
            ->getQuery()
            ->getSingleScalarResult());
    }

    public function createLocation(User $user, $ifnotexists = false) {
        if ($ifnotexists === false || $this->anyLocationExists($user) === false) {
            $loc = new Location($user);
            $loc->setPassword(bin2hex(random_bytes(2)));
            $loc->setCaption($this->translator->trans('Location') . " #" . ($this->totalCount($user) + 1));
            return $loc;
        }
        return null;
    }

    public function locationById($id) {
        $user = $this->sec->getToken()->getUser();

        if ($user === null) {
            return null;
        }

        return $this->rep->findOneBy(['user' => $user, 'id' => $id]);
    }
}
