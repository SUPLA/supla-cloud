<?php
/*
 src/AppBundle/Controller/AjaxController.php

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

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Supla\ServerCtrl;
use AppBundle\Supla\SuplaConst;

/**
 * @author Przemyslaw Zygmunt p.zygmunt@acsoftware.pl [AC SOFTWARE]
 * @Route("/ajax")
 */
class AjaxController extends Controller
{
	
	static public function jsonResponse($success, $result = array()) 
	{
		$result['success'] = $success;
		
		return new Response(json_encode($result), 200, array('Content-Type' => 'application/json'));
	}
	
	
	static function itemEdit($validator, $translator, $doctrine, $item, $message, $value)
	{
	
		$success = false;
	
		if ( $item !== null ) {
	
			$errors = $validator->validate($item);
	
			if (count($errors) > 0) {
	
				// Get first one
				$result = array('flash'=> array('title' => $translator->trans('Error'),
						'message' => $translator->trans($errors[0]->getMessage()),
						'type' => 'error')
				);
	
			} else {
	
				$doctrine->getManager()->flush();
	
				$result = array('flash'=> array('title' => $translator->trans('Success'),
						'message' => $translator->trans($message),
						'type' => 'success'),
						'value' => $value
				);
	
				$success = true;
	
			}
	
		}
	
		return AjaxController::jsonResponse($success, $result);
	}
    
    /**
     * @Route("/lngset/{_loc}", name="_ajaxlngset")
     */
    public function lngsetAction(Request $request, $_loc)
    {
    	$result = false;
    	
    	if ( in_array($_loc, array('en', 'pl', 'de', 'ru')) ) {
    		$request->getSession()->set('_locale', $_loc);
    		$result = true;
    	}
    	
    	return AjaxController::jsonResponse($result);
    }
   
    
    /**
     * @Route("/pwdgen/{len}", name="_ajax_pwdgen")
     */
    public function pwdgenAction($len)
    {
    	$len = intval($len);
    	
    	$pwd = '';
    	$success = false;
    
    	$len = (int)($len/2);

    	if ( $len > 0 ) {
    		
    		$generator = new SecureRandom();
    		$pwd = bin2hex($generator->nextBytes($len));
    		$success = true;
    		
    	}
 
    	return AjaxController::jsonResponse($success, array("pwd" => $pwd));
    }
    
    /**
     * @Route("/serverctrl-sensorstate/{iodevice_id}/{channel_id}", name="_ajax_serverctrl-sensorstate")
     */
    public function serverctrlSensorStateAction($iodevice_id, $channel_id)
    {
    	$user = $this->get('security.token_storage')->getToken()->getUser();
    	$value = (new ServerCtrl())->get_double_value($user->getId(), $iodevice_id, $channel_id);
    	
    	$dev_man = $this->get('iodevice_manager');
    	$channel = $dev_man->channelById($channel_id);
    	
    	if ( $channel !== null
    		 && $channel->getType() == SuplaConst::TYPE_SENSORNC ) {
    					
    			$value = $value == '1' ? '0' : '1';
    	}

    	
    	return AjaxController::jsonResponse(true, array('value' => $this->get('translator')->trans($value == '1' ? 'Close' : 'Open')));
    }
    
    /**
     * @Route("/serverctrl-tempval/{iodevice_id}/{channel_id}", name="_ajax_serverctrl-tempval")
     */
    public function serverctrlTempValAction($iodevice_id, $channel_id)
    {
    	$user = $this->get('security.token_storage')->getToken()->getUser();
    	$value = (new ServerCtrl())->get_temperature_value($user->getId(), $iodevice_id, $channel_id);
    	return AjaxController::jsonResponse(true, array('value' => $value === FALSE || $value < -273 ? '-' : number_format($value, 1)));
    }
    
    /**
     * @Route("/serverctrl-humidityval/{iodevice_id}/{channel_id}", name="_ajax_serverctrl-humiditypval")
     */
    public function serverctrlHumidityValAction($iodevice_id, $channel_id)
    {
    	$user = $this->get('security.token_storage')->getToken()->getUser();
    	$value = (new ServerCtrl())->get_humidity_value($user->getId(), $iodevice_id, $channel_id);
    	return AjaxController::jsonResponse(true, array('value' => $value === FALSE || $value < 0 ? '-' : number_format($value, 1)));
    }
    
    /**
     * @Route("/serverctrl-connstate", name="_ajax_serverctrl-connstate")
     */
    public function serverctrlConnStateAction(Request $request)
    {
    	$result = array();
    	$data = json_decode($request->getContent());
    
    	$c = $this->get('translator')->trans('Connected');
    	$d = $this->get('translator')->trans('Disconnected');
    	
    	$user = $this->get('security.token_storage')->getToken()->getUser();
    
    	if ( is_array($data->devids) ) {
    		$ids = array_unique($data->devids);
    		unset($data);
    		
    		$cids = (new ServerCtrl())->iodevice_connected($user->getId(), $ids); 
    		
    		foreach($ids as $id) {
    			$result[$id] = in_array($id, $cids) ? array('state' => 1, 'txt' => $c) : array('state' => 0, 'txt' => $d);
    		}
    	}
    
    	return AjaxController::jsonResponse(count($result) > 0, array('states' => $result));
    }
    
}

