<?php 
/*
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

namespace SuplaApiBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class AccessIDController extends RestController
{
	    	
	protected function getAccessIDS() {
	
		$result = array();
		$parent = $this->getParentUser();
			
		if ( $parent !== null ) {
			
			foreach($parent->getAccessIDS() as $aid) {
			
				$locations = array();
			
				foreach($aid->getLocations() as $location) {
					$locations[] = $location->getId();
				}
			
				$result[] = array(
						'id' => $aid->getId(),
						'password' => $aid->getPassword(),
						'caption' => $aid->getCaption(),
						'enabled' => $aid->getEnabled() === true ? true : false,
						'locations' => $locations,
				);
			}
			
		}
			
		return array('accessids' => $result);
	}

	

	/**
	 * @Rest\Get("/accessids")
	 */
	public function getAccessidsAction(Request $request)
	{
		return  $this->handleView($this->view($this->getAccessIDS(), Response::HTTP_OK));
	
	}
	
}

?>