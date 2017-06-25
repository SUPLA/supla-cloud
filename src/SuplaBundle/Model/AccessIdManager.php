<?php
/*
 src/SuplaBundle/Model/AccessIdManager.php

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
use SuplaBundle\Entity\AccessID;
use SuplaBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class AccessIdManager {
    protected $translator;
    protected $doctrine;
    protected $rep;
    protected $sec;

    public function __construct(Translator $translator, ManagerRegistry $doctrine, TokenStorage $security_token) {
        $this->translator = $translator;
        $this->doctrine = $doctrine;
        $this->rep = $doctrine->getRepository('SuplaBundle:AccessID');
        $this->sec = $security_token;
    }

    public function anyIdExists(User $user) {
        return $user !== null && ($user->getAccessIDS()->count() > 0 || $this->rep->findOneBy(['user' => $user]) !== null);
    }

    public function totalCount(User $user) {
        $qb = $this->rep->createQueryBuilder('a');

        return intval($qb->select('count(a.id)')
            ->where($qb->expr()->eq('a.user', ':user'))
            ->setParameter('user', $user->getId())
            ->getQuery()
            ->getSingleScalarResult());
    }

    public function createID(User $user, $ifnotexists = false) {
        if ($ifnotexists === false
            || $this->anyIdExists($user) === false
        ) {
            $aid = new AccessID($user);

            if ($aid !== null) {
                $aid->setPassword(bin2hex(random_bytes(4)));
                $aid->setCaption($this->translator->trans('Access Identifier') . " #" . ($this->totalCount($user) + 1));

                return $aid;
            }
        }

        return null;
    }

    public function accessIdById($id) {
        $user = $this->sec->getToken()->getUser();

        if ($user === null) {
            return null;
        }

        return $this->rep->findOneBy(['user' => $user, 'id' => $id]);
    }
}
