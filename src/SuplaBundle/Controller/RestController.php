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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Supla\SuplaConst;
use SuplaBundle\Supla\ServerCtrl;
use Doctrine\ORM\Query;


class RestController extends FOSRestController
{
	
	const ERROR_CODE_NOTFOUND          = 1;
	const ERROR_CODE_NOTALLOWED        = 2;
	const ERROR_CODE_UNAVAILABLE       = 3;
	const RECORD_LIMIT_PER_REQUEST     = 5000;
	
	protected $user;
	protected $parent;
	
	public function getUser() {
		
		if ($this->user === null) {
			$this->user = $this->container->get('security.token_storage')->getToken()->getUser();
		}
		return $this->user;
	}
	
	public function getParentUser() {
		
		if ($this->parent === null) {
			$this->parent = $this->getUser()->getParentUser();
		}
		return $this->parent;
	}
		
	private function resultView($success, $data = null, $error_code = null, $error_msg = '') {
		
		$success = $success === true ? true : false;

		$v['success'] = $success;
		
		if ( $success === false ) {
			$v['error'] = array('message' => $error_msg, 'code' => $error_code);
		}

		if ( $data !== null )
			$v['data'] = $data;
		
		return $this->view($v, Response::HTTP_OK);
	}
	    
    /**
     * @Rest\Get("/logout/{refreshToken}")
     */
    public function logoutAction(Request $request, $refreshToken)
    {
    	
    	$api_man = $this->container->get('api_manager');
    	
    	$ts = $this->container->get('security.token_storage')->getToken();
    	$api_man->userLogout($ts->getUser(), $ts->getToken(), $refreshToken);
    	 
    	return $this->handleView($this->resultView(true));
    
    }
    
    /**
     * @Rest\Get("/server/info")
     */
    public function getServerParamsAction(Request $request)
    {    	
    	$dt = new \DateTime();
    	 
    	$result = array('address' => $this->container->getParameter('supla_server'),
    			'time' => $dt,
    			'timezone' => array('name' => $dt->getTimezone()->getName(),
    			                    'offset' => $dt->getOffset()),
    	);
    	 
    	return $this->handleView($this->resultView(true, $result));
    
    }
    
    /**
     * @Rest\Get("/locations")
     */
    public function getLocationsAction(Request $request)
    {
    	$loc_man = $this->container->get('location_manager');
    	    	    	
    	return $this->handleView($this->resultView(true, $loc_man->locationsForApiUser($this->getUser())));
    } 
    
    /**
     * @Rest\Get("/accessids")
     */
    public function getAccessidsAction(Request $request)
    {
    	$aid_man = $this->container->get('accessid_manager');
    
    	return $this->handleView($this->resultView(true, $aid_man->accessidsForApiUser($this->getUser())));
    
    }
    
    /**
     * @Rest\Get("/iodevices")
     */
    public function getIOdevicesAction(Request $request)
    {
    	$iodev_man = $this->container->get('iodevice_manager');
    	
    	return $this->handleView($this->resultView(true, $iodev_man->ioDevicesForApiUser($this->getUser())));
    
    }
    
    private function ioDeviceById($devid) {
    	 
    	$devid = intval($devid, 0);
    	$iodev_man = $this->container->get('iodevice_manager');
        
    	$iodevice = $iodev_man->ioDeviceById($devid, $this->getParentUser());
    	 
    	if ( !($iodevice instanceof IODevice) ) {
    		
    		$error_code = RestController::ERROR_CODE_NOTFOUND;
    		$error_msg = 'Device not found';
    		    		 
    		return $this->handleView($this->resultView(false, null, $error_code, $error_msg));

    	}
    	
    	return $iodevice;
    } 
    
    /**
     * @Rest\Get("/iodevice/{devid}/connected")
     */
    public function getIODeviceConnectedAction(Request $request, $devid)
    {
    	if ( ($result = $this->ioDeviceById($devid)) instanceof IODevice ) {
    		    		
    		$cids = (new ServerCtrl())->iodevice_connected($this->getParentUser()->getId(), array($devid));
    		
    		return $this->handleView($this->resultView(true, array('connected' => in_array($devid, $cids) ? true : false)));
    	};
    	 
    	return $result;
    }
        
    /**
     * @Rest\Get("/iodevice/{devid}/enabled")
     */
    public function getIODeviceEnabledAction(Request $request, $devid)
    {
    	
    	if ( ($result = $this->ioDeviceById($devid)) instanceof IODevice ) {
    		return $this->handleView($this->resultView(true, array('enabled' => $result->getEnabled() ? true : false)));
    	};
    	
    	return $result;
    }
    
    private function channelById($channelid, $functions = null) {
    
    	$channelid = intval($channelid, 0);
    	$iodev_man = $this->container->get('iodevice_manager');
    
    	$channel = $iodev_man->channelById($channelid, $this->getParentUser());
    
    	if ( !($channel instanceof IODeviceChannel ) ) {
    
    		$error_code = RestController::ERROR_CODE_NOTFOUND;
    		$error_msg = 'Channel not found';
    		 
    		return $this->handleView($this->resultView(false, null, $error_code, $error_msg));
    
    	}
    	
    	if ( is_array($functions)
    		 && !in_array($channel->getFunction(), $functions) ) {
    	
    		 	$error_code = RestController::ERROR_CODE_NOTALLOWED;
    		 	$error_msg = 'Request is not allowed with selected channel function';
    		 	
    		 	return $this->handleView($this->resultView(false, null, $error_code, $error_msg));
    	}
    	 
    	return $channel;
    }
    
    private function getLogTempHumidityCountAction($th, $channelid)
    {
    	 
        $f = array();
    	
    	if ( $th === TRUE ) {
    		$f[] = SuplaConst::FNC_HUMIDITYANDTEMPERATURE;
    	} else {
    		$f[] = SuplaConst::FNC_THERMOMETER;
    	}
    	 
    	if ( ($result = $this->channelById($channelid, $f)) instanceof IODeviceChannel ) {
    
    		$em = $this->container->get('doctrine')->getManager();
    		$rep = $em->getRepository('SuplaBundle:'.($th === TRUE ? 'TempHumidityLogItem' : 'TemperatureLogItem'));
    
    		$query = $rep->createQueryBuilder('f')
    		->select('COUNT(f.id)')
    		->where('f.channel_id = :id')
    		->setParameter('id', $channelid)
    		->getQuery();
    
    		return $this->handleView($this->resultView(true,
    				array('count' => $query->getSingleScalarResult(),
    						'record_limit_per_request' => RestController::RECORD_LIMIT_PER_REQUEST,
    
    				)));
    	};
    
    	return $result;
    	 
    } 
    
    /**
     * @Rest\Get("/channel/{channelid}/log/temp/count")
     */
    public function getLogTempCountAction(Request $request, $channelid)
    {
    	 
    	return $this->getLogTempHumidityCountAction(FALSE, $channelid);
    	
    }
    
    private function getLogTempHumidityItemsAction($th, $channelid, $offset, $limit)
    {
    	 
    	$f = array();
    	
    	if ( $th === TRUE ) {
    		$f[] = SuplaConst::FNC_HUMIDITYANDTEMPERATURE;
    	} else {
    		$f[] = SuplaConst::FNC_THERMOMETER;
    	}
    
    	if ( ($result = $this->channelById($channelid, $f)) instanceof IODeviceChannel ) {
    		 
    		$offset = intval($offset, 0);
    		$limit = intval($limit, 0);
    
    		if ( $limit <= 0 )
    			$limit = RestController::RECORD_LIMIT_PER_REQUEST;
    
    			$iodev_man = $this->container->get('iodevice_manager');
    
    			if ( $th === TRUE ) {
    				$result = $iodev_man->temperatureAndHumidityLogItems($channelid, $offset, $limit);
    			} else {
    				$result = $iodev_man->temperatureLogItems($channelid, $offset, $limit);
    			}
    			
    			return $this->handleView($this->resultView(true, $result));
    	};
    	 
    	return $result;
    
    
    }
    
    /**
     * @Rest\Get("/channel/{channelid}/log/temp/items/{offset}/{limit}")
     */
    public function getLogTempItemsAction(Request $request, $channelid, $offset, $limit)
    {
        	
    	return $this->getLogTempHumidityItemsAction(FALSE, $channelid, $offset, $limit);
    
    }
    
    /**
     * @Rest\Get("/channel/{channelid}/log/temp-hum/count")
     */
    public function getLogTempHumCountAction(Request $request, $channelid)
    {
    	
    	return $this->getLogTempHumidityCountAction(TRUE, $channelid);
    }
    
    /**
     * @Rest\Get("/channel/{channelid}/log/temp-hum/items/{offset}/{limit}")
     */
    public function getLogTempHumItemsAction(Request $request, $channelid, $offset, $limit)
    {
    	
    	return $this->getLogTempHumidityItemsAction(TRUE, $channelid, $offset, $limit);
    }
    
    private function getChannelValue($channelid, $compat, $value_type) {
    	
    	
    	if ( ($result = $this->channelById($channelid, $compat)) instanceof IODeviceChannel ) {
    		 
    		$serverCtrl = new ServerCtrl();
    		$value = FALSE;
    		
    		switch($value_type) {
    			case 'onoff':
    			case 'hilo':
    				$value = $serverCtrl->get_char_value($result->getUser()->getId(), $result->getIoDevice()->getId(), $channelid);
    				break;
    			case 'temperature':
    				$value = $serverCtrl->get_temperature_value($result->getUser()->getId(), $result->getIoDevice()->getId(), $channelid);
    				break;
    			case 'humidity':
    				$value = $serverCtrl->get_humidity_value($result->getUser()->getId(), $result->getIoDevice()->getId(), $channelid);
    				break;
    			case 'temp-hum':
    				
    				$t = $serverCtrl->get_temperature_value($result->getUser()->getId(), $result->getIoDevice()->getId(), $channelid);
    				
    				if ( $t !== FALSE ) {
    					
    					$h = $serverCtrl->get_humidity_value($result->getUser()->getId(), $result->getIoDevice()->getId(), $channelid);
    					
    					if ( $h !== FALSE ) {
    						$value = array('temperature' => $t, 'humidity' => $h);
    					}
    				}
    						
    				break;
    				
    			case 'rgbw':
    				$value = $serverCtrl->get_rgbw_value($result->getUser()->getId(), $result->getIoDevice()->getId(), $channelid);
    				break;
    				
    			case 'color':
    				$value = $serverCtrl->get_rgbw_value($result->getUser()->getId(), $result->getIoDevice()->getId(), $channelid);
    				
    				if ( $value !== FALSE && array_key_exists('color', $value) )
    					$value = array('color' => $value['color']);
    				
    				break;
    				
    			case 'color-brightness':
    				$value = $serverCtrl->get_rgbw_value($result->getUser()->getId(), $result->getIoDevice()->getId(), $channelid);
    				
    				if ( $value !== FALSE && array_key_exists('color_brightness', $value) )
    					$value = array('color_brightness' => $value['color_brightness']);
    				
    				break;
    				
    			case 'brightness':
    				$value = $serverCtrl->get_rgbw_value($result->getUser()->getId(), $result->getIoDevice()->getId(), $channelid);
    				
    				if ( $value !== FALSE && array_key_exists('brightness', $value) )
    					$value = array('brightness' => $value['brightness']);
    				
    				break;
    						
    		}
    		
    		$serverCtrl->disconnect();
 
    		if ( $value === FALSE ) {
    			 
    			$error_code = RestController::ERROR_CODE_UNAVAILABLE;
    			$error_msg = 'Channel is unavailable';
    	
    			return $this->handleView($this->resultView(false, null, $error_code, $error_msg));
    			 
    		}
    		
    	    switch($value_type) {    	    		
    			case 'onoff':
    				$value = array('on' => $value == '1' ? true : false);
    				break;
    			case 'hilo':
    				$value = array('hi' => $value == '1' ? true : false);
    				break;
    			case 'temperature':
    				$value = array('temperature' => $value);
    				break;
    			case 'humidity':
    				$value = array('humidity' => $value);
    				break;
    		}
    	   	
    		return $this->handleView($this->resultView(true, $value));
    	};
    	 
    	return $result;
    	
    }
    
    /**
     * @Rest\Get("/channel/{channelid}/value/on")
     */
    public function getChannelValueOnAction(Request $request, $channelid)
    {
    	$compat = array(SuplaConst::FNC_POWERSWITCH, 
    			   SuplaConst::FNC_LIGHTSWITCH);
    	 
    	return $this->getChannelValue($channelid, $compat, 'onoff');

    }
    
    /**
     * @Rest\Get("/channel/{channelid}/value/hi")
     */
    public function getChannelValueHiAction(Request $request, $channelid)
    {
    	$compat = array(SuplaConst::FNC_OPENINGSENSOR_GATEWAY,
		    			SuplaConst::FNC_OPENINGSENSOR_GATE,
		    			SuplaConst::FNC_OPENINGSENSOR_GARAGEDOOR,
		    			SuplaConst::FNC_NOLIQUIDSENSOR,
		    			SuplaConst::FNC_OPENINGSENSOR_DOOR,
		    			SuplaConst::FNC_OPENINGSENSOR_ROLLERSHUTTER,
    	);
    
    	return $this->getChannelValue($channelid, $compat, 'hilo');
    
    }
    
    /**
     * @Rest\Get("/channel/{channelid}/value/temperature")
     */
    public function getChannelValueTemperatureAction(Request $request, $channelid)
    {
    	$compat = array(SuplaConst::FNC_THERMOMETER,
    			SuplaConst::FNC_HUMIDITYANDTEMPERATURE);
    	
    	return $this->getChannelValue($channelid, $compat, 'temperature');
    }
    
    /**
     * @Rest\Get("/channel/{channelid}/value/humidity")
     */
    public function getChannelValueHumidityAction(Request $request, $channelid)
    {
    	$compat = array(SuplaConst::FNC_HUMIDITY,
    			SuplaConst::FNC_HUMIDITYANDTEMPERATURE);
    	 
    	return $this->getChannelValue($channelid, $compat, 'humidity');
    }
    
    /**
     * @Rest\Get("/channel/{channelid}/value/temp-hum")
     */
    public function getChannelValueTempHumAction(Request $request, $channelid)
    {
    	$compat = array(SuplaConst::FNC_HUMIDITYANDTEMPERATURE);
    	
    	return $this->getChannelValue($channelid, $compat, 'temp-hum');
    }
    
    /**
     * @Rest\Get("/channel/{channelid}/value/rgbw")
     */
    public function getChannelValueRGBWAction(Request $request, $channelid)
    {
    
    	$compat = array(SuplaConst::FNC_DIMMER,
    			        SuplaConst::FNC_RGBLIGHTING,
    			        SuplaConst::FNC_DIMMERANDRGBLIGHTING,
    	);
    	 
    	return $this->getChannelValue($channelid, $compat, 'rgbw');
    
    }
    
    /**
     * @Rest\Get("/channel/{channelid}/value/color")
     */
    public function getChannelValueColorAction(Request $request, $channelid)
    {
    
    	$compat = array(SuplaConst::FNC_RGBLIGHTING,
    			SuplaConst::FNC_DIMMERANDRGBLIGHTING,
    	);
    
    	return $this->getChannelValue($channelid, $compat, 'color');
    
    }
    
    /**
     * @Rest\Get("/channel/{channelid}/value/color-brightness")
     */
    public function getChannelValueColorBrightnessAction(Request $request, $channelid)
    {
    
    	$compat = array(SuplaConst::FNC_RGBLIGHTING,
    			SuplaConst::FNC_DIMMERANDRGBLIGHTING,
    	);
    
    	return $this->getChannelValue($channelid, $compat, 'color-brightness');
    
    }
    
    /**
     * @Rest\Get("/channel/{channelid}/value/brightness")
     */
    public function getChannelValueBrightnessAction(Request $request, $channelid)
    {
    
    	$compat = array(SuplaConst::FNC_DIMMER,
    			SuplaConst::FNC_DIMMERANDRGBLIGHTING,
    	);
    
    	return $this->getChannelValue($channelid, $compat, 'brightness');
    
    }
    
}

?>