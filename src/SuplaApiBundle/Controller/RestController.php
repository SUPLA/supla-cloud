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
	
	const ERROR_CODE_NOTFOUND                     = 1;
	const ERROR_CODE_NOTALLOWED                   = 2;
	const ERROR_CODE_UNKNOWN_OR_UNAVAILABLE       = 3;
	const ERROR_CODE_UNAUTHORIZED                 = 4;
	const ERROR_CODE_OUTOFRANGE                   = 5;
	
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
     * @Rest\Get("/server-info")
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
     * @Rest\Get("/iodevices/{devid}/connected")
     */
    public function getIODevicesConnectedAction(Request $request, $devid)
    {
    	if ( ($result = $this->ioDeviceById($devid)) instanceof IODevice ) {
    		    		
    		$cids = (new ServerCtrl())->iodevice_connected($this->getParentUser()->getId(), array($devid));
    		
    		return $this->handleView($this->resultView(true, array('connected' => in_array($devid, $cids) ? true : false)));
    	};
    	 
    	return $result;
    }
        
    /**
     * @Rest\Get("/iodevices/{devid}/enabled")
     */
    public function getIODevicesEnabledAction(Request $request, $devid)
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
     * @Rest\Get("/channels/{channelid}/log-temp-count")
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
     * @Rest\Get("/channels/{channelid}/log-temp-items/{offset}/{limit}")
     */
    public function getLogTempItemsAction(Request $request, $channelid, $offset, $limit)
    {
        	
    	return $this->getLogTempHumidityItemsAction(FALSE, $channelid, $offset, $limit);
    
    }
    
    /**
     * @Rest\Get("/channels/{channelid}/log-temphum-count")
     */
    public function getLogTempHumCountAction(Request $request, $channelid)
    {
    	
    	return $this->getLogTempHumidityCountAction(TRUE, $channelid);
    }
    
    /**
     * @Rest\Get("/channels/{channelid}/log-temphum-items/{offset}/{limit}")
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
    				
    			case 'rgb':
    				$value = $serverCtrl->get_rgbw_value($result->getUser()->getId(), $result->getIoDevice()->getId(), $channelid);
    				
    				if (  $value !== FALSE
    					  && array_key_exists('color', $value)
    					  && array_key_exists('color_brightness', $value) ) {
    					

    						$value = array('color' => $value['color'], 
    								       'color_brightness' => $value['color_brightness']);
    	
    				}
    				
    				break;
    				
    			case 'color':
    				$value = $serverCtrl->get_rgbw_value($result->getUser()->getId(), $result->getIoDevice()->getId(), $channelid);
    				
    				if ( $value !== FALSE && array_key_exists($value_type, $value) )
    					$value = array('color' => $value[$value_type]);
    				
    				break;
    				
    			case 'color_brightness':
    				$value = $serverCtrl->get_rgbw_value($result->getUser()->getId(), $result->getIoDevice()->getId(), $channelid);
    				
    				if ( $value !== FALSE && array_key_exists($value_type, $value) )
    					$value = array($value_type => $value[$value_type]);
    				
    				break;
    				
    			case 'brightness':
    				$value = $serverCtrl->get_rgbw_value($result->getUser()->getId(), $result->getIoDevice()->getId(), $channelid);
    				
    				if ( $value !== FALSE && array_key_exists($value_type, $value) )
    					$value = array($value_type => $value[$value_type]);
    				
    				break;
    			
    			case 'distance':
    				$value = $serverCtrl->get_distance_value($result->getUser()->getId(), $result->getIoDevice()->getId(), $channelid);
    				break;
    				
    			case 'depth':
    				$value = $serverCtrl->get_depth_value($result->getUser()->getId(), $result->getIoDevice()->getId(), $channelid);
    				break;
    					
    		}
    		
    		$serverCtrl->disconnect();
 
    		if ( $value === FALSE ) {
    			 
    			$error_code = RestController::ERROR_CODE_UNKNOWN_OR_UNAVAILABLE;
    			$error_msg = 'Unknown error or channel is unavailable';
    	
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
    			case 'humidity':
    			case 'depth':
    			case 'distance':
    				$value = array($value_type => $value);
    				break;
    		}
    	   	
    		return $this->handleView($this->resultView(true, $value));
    	};
    	 
    	return $result;
    	
    }
    
    private function setChannelValue($channelid, $compat, $value_type, $value) {
    	 
    	 
    	if ( ($result = $this->channelById($channelid, $compat)) instanceof IODeviceChannel ) {
    		 
    		$serverCtrl = new ServerCtrl();
    		
    		$user_id = $this->getParentUser()->getId();
    		
    		if ( TRUE === $serverCtrl->oauth_authorize($user_id, 
    				                                   $this->container->get('security.token_storage')->getToken()->getToken()) ) {
    			
    				switch($value_type) {
    				   case 'char':
    				       $result = $serverCtrl->set_char_value($user_id, $result->getIoDevice()->getId(), $channelid, $value);
    				   break;             
    				   case 'rgbw':
    				   	   $result = $serverCtrl->set_rgbw_value($user_id, $result->getIoDevice()->getId(), $channelid, $value['color'], $value['color_brightness'], $value['brightness']);
    				}        
    				
    				if ( $result !== TRUE ) {
    
    					$error_code = RestController::ERROR_CODE_UNKNOWN_OR_UNAVAILABLE;
    					$error_msg = 'Unknown error or channel is unavailable';
    						 
    					return $this->handleView($this->resultView(false, null, $error_code, $error_msg));
    				}
    				                                   	
    			
    		} else {
    			$error_code = RestController::ERROR_CODE_UNAUTHORIZED;
    			$error_msg = 'Unauthorized';
    			
    			return $this->handleView($this->resultView(false, null, $error_code, $error_msg));
    		}
    		
    			
    		return $this->handleView($this->resultView(true, null));
    	};
    
    	return $result;
    	 
    }
    
    /**
     * @Rest\Get("/channels/{channelid}/on")
     */
    public function channelsGetOnAction(Request $request, $channelid)
    {
    	$compat = array(SuplaConst::FNC_POWERSWITCH, 
    			   SuplaConst::FNC_LIGHTSWITCH);
    	 
    	return $this->getChannelValue($channelid, $compat, 'onoff');

    }
    
    /**
     * @Rest\Get("/channels/{channelid}/hi")
     */
    public function channelsGetHiAction(Request $request, $channelid)
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
     * @Rest\Get("/channels/{channelid}/temperature")
     */
    public function channelsGetTemperatureAction(Request $request, $channelid)
    {
    	$compat = array(SuplaConst::FNC_THERMOMETER,
    			SuplaConst::FNC_HUMIDITYANDTEMPERATURE);
    	
    	return $this->getChannelValue($channelid, $compat, 'temperature');
    }
    
    /**
     * @Rest\Get("/channels/{channelid}/humidity")
     */
    public function channelsGetHumidityAction(Request $request, $channelid)
    {
    	$compat = array(SuplaConst::FNC_HUMIDITY,
    			SuplaConst::FNC_HUMIDITYANDTEMPERATURE);
    	 
    	return $this->getChannelValue($channelid, $compat, 'humidity');
    }
    
    /**
     * @Rest\Get("/channels/{channelid}/temp-hum")
     */
    public function channelsGetTempHumAction(Request $request, $channelid)
    {
    	$compat = array(SuplaConst::FNC_HUMIDITYANDTEMPERATURE);
    	
    	return $this->getChannelValue($channelid, $compat, 'temp-hum');
    }
    
    /**
     * @Rest\Get("/channels/{channelid}/rgbw")
     */
    public function channelsGetRGBWAction(Request $request, $channelid)
    {
    
    	$compat = array(SuplaConst::FNC_DIMMER,
    			        SuplaConst::FNC_RGBLIGHTING,
    			        SuplaConst::FNC_DIMMERANDRGBLIGHTING,
    	);
    	 
    	return $this->getChannelValue($channelid, $compat, 'rgbw');
    
    }
    
    /**
     * @Rest\Get("/channels/{channelid}/rgb")
     */
    public function channelsGetRGBAction(Request $request, $channelid)
    {
    
    	$compat = array(SuplaConst::FNC_RGBLIGHTING,
    			SuplaConst::FNC_DIMMERANDRGBLIGHTING,
    	);
    
    	return $this->getChannelValue($channelid, $compat, 'rgb');
    
    }
    
    /**
     * @Rest\Get("/channels/{channelid}/color")
     */
    public function getChannelColorAction(Request $request, $channelid)
    {
    
    	$compat = array(SuplaConst::FNC_RGBLIGHTING,
    			SuplaConst::FNC_DIMMERANDRGBLIGHTING,
    	);
    
    	return $this->getChannelValue($channelid, $compat, 'color');
    
    }
    
    /**
     * @Rest\Get("/channels/{channelid}/color-brightness")
     */
    public function getChannelColorBrightnessAction(Request $request, $channelid)
    {
    
    	$compat = array(SuplaConst::FNC_RGBLIGHTING,
    			SuplaConst::FNC_DIMMERANDRGBLIGHTING,
    	);
    
    	return $this->getChannelValue($channelid, $compat, 'color_brightness');
    
    }
    
    /**
     * @Rest\Get("/channels/{channelid}/brightness")
     */
    public function channelsGetBrightnessAction(Request $request, $channelid)
    {
    
    	$compat = array(SuplaConst::FNC_DIMMER,
    			SuplaConst::FNC_DIMMERANDRGBLIGHTING,
    	);
    
    	return $this->getChannelValue($channelid, $compat, 'brightness');
    
    }
    
    /**
     * @Rest\Get("/channels/{channelid}/depth")
     */
    public function getChannelDepthAction(Request $request, $channelid)
    {
    
    	$compat = array(SuplaConst::FNC_DEPTHSENSOR);
    
    	return $this->getChannelValue($channelid, $compat, 'depth');
    
    }
    
    /**
     * @Rest\Get("/channels/{channelid}/distance")
     */
    public function channelsGetDistanceAction(Request $request, $channelid)
    {
    
    	$compat = array(SuplaConst::FNC_DISTANCESENSOR);
    
    	return $this->getChannelValue($channelid, $compat, 'distance');
    
    }
    
    /**
     * @Rest\Get("/channels/{channelid}/depth")
     */
    public function channelsGetDepthAction(Request $request, $channelid)
    {
    
    	$compat = array(SuplaConst::FNC_DISTANCESENSOR);
    
    	return $this->getChannelValue($channelid, $compat, 'depth');
    
    }
    
    private function channelsSetRGBW($channelid, $compat, $color, $color_brightness, $brightness) {
    	    	 
    	if ( $color < 0
    			|| $color > 0xffffff
    			|| $color_brightness < 0
    			|| $color_brightness > 100
    			|| $brightness < 0
    			|| $brightness > 100 ) {
    	
    				$error_code = RestController::ERROR_CODE_OUTOFRANGE;
    				$error_msg = 'Value out of range';
    				 
    				return $this->handleView($this->resultView(false, null, $error_code, $error_msg));
    	
    	}
    			 
    	    			 
    	$params = array('color' => $color,
    					'color_brightness' => $color_brightness,
    					'brightness' => $brightness
    	);
    			 
    	return $this->setChannelValue($channelid, $compat, 'rgbw', $params);
    			
    }
    
    /**
     * @Rest\Post("/channels/{channelid}/rgbw")
     */
    public function channelsSetRGBWAction(Request $request, $channelid)
    {
    	$data = json_decode($request->getContent());
    	
    	$compat = array(SuplaConst::FNC_DIMMER,
    			SuplaConst::FNC_RGBLIGHTING,
    			SuplaConst::FNC_DIMMERANDRGBLIGHTING,
    			 
    	);

    	return $this->channelsSetRGBW($channelid, $compat, intval(@$data->color, 0), intval(@$data->color_brightness), intval(@$data->brightness));    
    }
    
    /**
     * @Rest\Post("/channels/{channelid}/rgb")
     */
    public function channelsSetRGBAction(Request $request, $channelid)
    {
    	$data = json_decode($request->getContent());
    	
    	$compat = array(SuplaConst::FNC_RGBLIGHTING,
    			SuplaConst::FNC_DIMMERANDRGBLIGHTING,
    	
    	);
    
    	return $this->channelsSetRGBW($channelid, $compat, intval(@$data->color, 0), intval(@$data->color_brightness), 0);
    }
    
    /**
     * @Rest\Post("/channels/{channelid}/brightness")
     */
    public function channelsSetBrightnessAction(Request $request, $channelid)
    {
    	$data = json_decode($request->getContent());
    	
    	$compat = array(SuplaConst::FNC_DIMMER);
    
    	return $this->channelsSetRGBW($channelid, $compat, 0, 0, intval(@$data->brightness));
    }

    private function channelTurnOnOff($channelid, $on)
    {
    
    	$compat = array(SuplaConst::FNC_POWERSWITCH,
    			SuplaConst::FNC_LIGHTSWITCH
    	);
    
    	return $this->setChannelValue($channelid, $compat, 'char', $on);
    
    }
    
    /**
     * @Rest\Post("/channels/{channelid}/turn-on")
     */
    public function channelTurnOnAction(Request $request, $channelid)
    {
    
    	 return $this->channelTurnOnOff($channelid, 1);
    }
    
    /**
     * @Rest\Post("/channels/{channelid}/turn-off")
     */
    public function channelTurnOffAction(Request $request, $channelid)
    {
    
    	return $this->channelTurnOnOff($channelid, 0);
    }
    
    
    /**
     * @Rest\Post("/channels/{channelid}/open")
     */
    public function channelOpenAction(Request $request, $channelid)
    {
    	$compat = array(SuplaConst::FNC_CONTROLLINGTHEGATEWAYLOCK,
    			SuplaConst::FNC_CONTROLLINGTHEDOORLOCK,
    			
    	);
    		
    	return $this->setChannelValue($channelid, $compat, 'char', 1);
    }
    
    /**
     * @Rest\Post("/channels/{channelid}/open-close")
     */
    public function channelOpenCloseAction(Request $request, $channelid)
    {
    	
    	$compat = array(SuplaConst::FNC_CONTROLLINGTHEGATE,
    			SuplaConst::FNC_CONTROLLINGTHEGARAGEDOOR,
    			 
    	);
    
    	return $this->setChannelValue($channelid, $compat, 'char', 1);
    }
    
    private function channelShutRevealStop($channelid, $v)
    {
    
    	$compat = array(SuplaConst::FNC_CONTROLLINGTHEROLLERSHUTTER);
    
    	return $this->setChannelValue($channelid, $compat, 'char', $v);
    
    }
    
    /**
     * @Rest\Post("/channels/{channelid}/shut")
     */
    public function channelShutAction(Request $request, $channelid)
    {
    
    	return $this->channelShutRevealStop($channelid, 1);
    }
    
    /**
     * @Rest\Post("/channels/{channelid}/reveal")
     */
    public function channelRevealAction(Request $request, $channelid)
    {
    
    	return $this->channelShutRevealStop($channelid, 2);
    }
    
    /**
     * @Rest\Post("/channels/{channelid}/stop")
     */
    public function channelStopAction(Request $request, $channelid)
    {
    
    	return $this->channelShutRevealStop($channelid, 0);
    }
    
}

?>