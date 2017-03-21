<?php
/*
 src/SuplaBundle/Controller/LocationController.php

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

namespace SuplaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SuplaBundle\Form\Type\LocationType;
use SuplaBundle\Form\Type\AssignType;
use SuplaBundle\Entity\Location;
use SuplaBundle\Supla\ServerCtrl;

/**
 * @Route("/loc")
 */
class LocationController extends Controller
{
	
	private function user_reconnect() {
		$user = $this->get('security.token_storage')->getToken()->getUser();
		(new ServerCtrl())->reconnect($user->getId());
	}
	
	private function getLocationById($id) 
	{
		$loc_man = $this->get('location_manager');
		return $loc_man->locationById($id);
	}
	
	private function getLocationDetails($id) {
	
		$loc = $this->getLocationById($id);
			
		if ( $loc !== null ) {
	
			return $this->get('templating')->render('SuplaBundle:Location:locdetails.html.twig',
					array('location' => $loc,
							'aids' => $loc->getAccessIds(),
							'iodevices' => $loc->getIoDevices(),
							'iodevices_ol' => $loc->getIoDevicesByOriginalLocation(),
					));
		};
	
		return null;
	}

	
    /**
     * @Route("/", name="_loc_list")
     */
    public function listAction()
    {
    	
    	$user = $this->get('security.token_storage')->getToken()->getUser();
    	$details = '';
    	 
    	$id = intval($this->get('session')->get('_loc_details_lastid'));
    	

    	if ( $this->getLocationById($id) === null
    			&& $user->getLocations()->count() > 0 ) {
    				$id = $user->getLocations()->get($user->getLocations()->count()-1)->getId();
    			}				
    	

    	if ( $id !== null && $id !== 0 ) {
    		$details = $this->getLocationDetails($id);
    			
    		if ( $details === null )
    			$details = '';
    	}
    	 
    	return $this->render('SuplaBundle:Location:loclist.html.twig',
    			array('locations' => $user->getLocations(),
    					'loc_selected' => $id === null ? 0 : $id,
    					'details' => $details
    			));
    	
    }
    
    /**
     * @Route("/{id}/view", name="_loc_item")
     */
    public function itemAction($id)
    {
    	$loc = $this->getLocationById($id);
    	 
    	if ( $aid !== null )
    		$this->get('session')->set('_loc_details_lastid', $loc->getId());
    	 
    	return $this->redirectToRoute("_loc_list");
    	
    }
    
    /**
     * @Route("/new", name="_loc_new")
     */
    public function newAction()
    {
    	$user = $this->get('security.token_storage')->getToken()->getUser();
    	$loc_man = $this->get('location_manager');
    	
    
    	if ( $user->getLimitLoc() > 0 
    	     && $loc_man->totalCount($user) >= $user->getLimitLoc() ) {
    	     	
    	     	$this->get('session')->getFlashBag()->add('error', array('title' => 'Stop', 'message' => 'Location limit has been exceeded'));
    	     	
    	} else {
    		
    		$loc = $loc_man->createLocation($user);
    		 
    		if ( $loc !== null ) {
    			$m = $this->get('doctrine')->getManager();
    			$m->persist($loc);
    			$m->flush();
    			$this->get('session')->getFlashBag()->add('success', array('title' => 'Success', 'message' => 'New location has been created'));
    		} else {
    			$this->get('session')->getFlashBag()->add('error', array('title' => 'Error', 'message' => 'Unknown error'));
    		}	
    	} 

    	
    	 return $this->redirectToRoute("_loc_list");
    }
    
    /**
     * @Route("/{id}/view", name="_loc_item")
     */
    public function itemViewAction(Request $request, $id)
    {
    	$loc = $this->getLocationById($id);
    	 
    	if ( $loc !== null )
    		$this->get('session')->set('_loc_details_lastid', $loc->getId());
    	 
    	return $this->redirectToRoute("_loc_list");
    }
    
    /**
     * @Route("/{id}/delete", name="_loc_item_delete")
     */
    public function itemDeleteAction($id)
    {
    	$loc = $this->getLocationById($id);
    	 
    	if ( $loc === null )
    		return $this->redirectToRoute("_loc_list");
    	
    	if ( $loc->getIoDevices()->count() > 0
    		 || $loc->getIoDevicesByOriginalLocation()->count() > 0 ) {
    		$this->get('session')->getFlashBag()->add('error', array('title' => 'Stop', 'message' => 'Remove all the associated devices before you delete this location'));
    		return $this->redirectToRoute("_loc_item", array('id' => $loc->getId()));
    	}
    	
    	$m = $this->get('doctrine')->getManager();
    	$m->remove($loc);
    	$m->flush();
    	
    	$this->user_reconnect();
    	
    	$this->get('session')->getFlashBag()->add('warning', array('title' => 'Information', 'message' => 'Location has been deleted'));
    	return $this->redirectToRoute("_loc_list");
    }
    
    /**
     * @Route("/{id}/assignaid", name="_loc_assignaid")
     */
    public function assignAidAction(Request $request, $id)
    {
    	$loc = $this->getLocationById($id);
    	
    	if ( $loc === null )
    		return $this->redirectToRoute("_loc_list");
    	
    	$user = $this->get('security.token_storage')->getToken()->getUser();
    	
    	
    	$form = $this->createForm(AssignType::class,
    			null,
    			array('cancel_url'=>$this->generateUrl('_loc_item', array('id' => $loc->getId()))));
    	
    	$form->handleRequest($request);
    	 
    	if ($form->isSubmitted() && $form->isValid()) {

    		$sel_aid = $request->request->get('aid');
    		$aids = $loc->getAccessIds();
    		
    		$aid_man = $this->get('accessid_manager');
    		$loc_man = $this->get('location_manager');
    		
    		
    		// remove
    		foreach($aids->getKeys() as $key) {
    			
    			$aid = $aids->get($key);
	 
    			if ( $aid !== null
    				 && ( is_array($sel_aid) === false
    							|| array_key_exists($aid->getId(), $sel_aid) == false
    							|| $sel_aid[$aid->getId()] != '1' ) ) {
    								$aids->remove($key);
    		    }
    		}
    		    		
    		
    		// add new
    		if ( is_array($sel_aid) === true )
    			foreach($sel_aid as $key=>$value)
    				if ( $value == '1' ) {
    						
    					$aid = $aid_man->accessIdById(intval($key));
    					if ( $aid !== null
    							&& $aids->contains($aid) === false ) {
    								$aids->add($aid);
    							}
    								
    				}
    		
    		$m = $this->get('doctrine')->getManager();
    		$m->flush();
    		
    		$this->user_reconnect();
    		
    		$this->get('session')->getFlashBag()->add('success', array('title' => 'Success', 'message' => 'Data saved!'));
    			
    		return $this->redirectToRoute("_loc_item", array('id' => $loc->getId()));
    	}
    	
    	return $this->render('SuplaBundle:Location:assignaid.html.twig',
    			array('form' => $form->createView(),
    				  'location' => $loc,
    				  'accessids' => $user->getAccessIds(),
    				  'selected' => $loc->getAccessIds()
    			));
    }
    
    /**
     * @Route("/{id}/ajax/assign_list", name="_loc_ajax_assign_list")
     */
    public function ajaxAssignAidList(Request $request, $id)
    {
    	$html = null;
    	$loc = $this->getLocationById($id);
    	 
    	if ( $loc !== null ) {
    
    		$user = $this->get('security.token_storage')->getToken()->getUser();
    		 
    		 
    		$form = $this->createForm(AssignType::class,
    				null,
    				array('cancel_url'=>$this->generateUrl('_loc_list'),
    						'action'=>$this->generateUrl('_loc_assignaid', array('id'=>$loc->getId()))
    				)
    		);
    		 
    		$html = $this->get('templating')->render('SuplaBundle:Location:assignaid.html.twig',
    				array('form' => $form->createView(),
    						'location' => $loc,
    						'aids' => $user->getAccessIds(),
    						'selected' => $loc->getAccessIds()
    				));
    	}
    	 
    
    	return AjaxController::jsonResponse($html !== null, array('html'=>$html));
    }
        
    /**
     * @Route("/{id}/ajax/getdetails", name="_loc_ajax_getdetails")
     */
    public function ajaxGetDetails(Request $request, $id)
    {
    	$result = false;
    	$html = null;
    	 
    	$html = $this->getLocationDetails($id);
    	 
    	if ( $html !== null )
    		$this->get('session')->set('_loc_details_lastid', intval($id));
    	 
    	 
    	return AjaxController::jsonResponse($html !== null, array('html'=>$html));
    
    }

    private function ajaxItemEdit(Location $loc, $message, $value) 
    {
    	$result = AjaxController::itemEdit($this->get('validator'), $this->get('translator'), $this->get('doctrine'), $loc, $message, $value);
    	$this->user_reconnect();
    	return $result;
    }
    
    
    /**
     * @Route("/{id}/ajax/setenabled/{enabled}", name="_loc_ajax_setenabled")
     */
    public function ajaxSetEnabled(Request $request, $id, $enabled)
    {
    	$loc = $this->getLocationById($id);
    
    	if ( $loc !== null )
    		$loc->setEnabled($enabled == '1');
    	 
    	return $this->ajaxItemEdit($loc,
    			'Location has been '.($enabled == '1' ? 'enabled' : 'disabled'),
    			$this->get('translator')->trans($enabled == '1' ? 'Enabled' : 'Disabled')
    	);
    	 
    }
    
    private function ajaxItemSet(Request $request, $id, $caption, $field) {
    	 
    	$loc = $this->getLocationById($id);
    	 
    	if ( $loc !== null ) {
    		$data = json_decode($request->getContent());
    
    		if ( $caption === true )
    			$loc->setCaption(@$data->value);
    		else
    			$loc->setPassword(@$data->value);
    	}
    	 
    	return $this->ajaxItemEdit($loc,
    			$field.' has been changed',
    			null
    	);
    }
    
    /**
     * @Route("/{id}/ajax/setcaption", name="_loc_ajax_setcaption")
     */
    public function ajaxSetCaption(Request $request, $id)
    {
    	return $this->ajaxItemSet($request, $id, true, 'Caption') ;
    }
    
    /**
     * @Route("/{id}/ajax/setpwd", name="_loc_ajax_setpwd")
     */
    public function ajaxSetPwd(Request $request, $id)
    {
    	return $this->ajaxItemSet($request, $id, false, 'Password') ;
    }
}
