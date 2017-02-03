<?php
/*
 src/SuplaBundle/Provider/OAuth/UserProvider.php

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

namespace SuplaBundle\Provider\OAuth;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class UserProvider implements UserProviderInterface
{
	
	  protected $api_manager;

      public function __construct($api_manager){
      	  $this->api_manager = $api_manager;
      }

      public function loadUserByUsername($username)
      {
      	return $this->api_manager->getAPIUserByName($username);
      }

      public function refreshUser(UserInterface $user)
      {
          $class = get_class($user);
          if (!$this->supportsClass($class)) {
              throw new UnsupportedUserException(
                  sprintf(
                      'Instances of "%s" are not supported.',
                      $class
                  )
              );
          }

          return $this->userRepository->find($user->getId());
      }

      public function supportsClass($class)
      {
          return $class === 'SuplaBundle\Entity\OAuth\User';
      }
}