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
use Symfony\Component\HttpKernel\Exception\HttpException;
use SuplaBundle\Supla\SuplaConst;
use SuplaBundle\Supla\ServerCtrl;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\IODeviceChannel;

class ChannelController extends RestController
{
	
	const RECORD_LIMIT_PER_REQUEST     = 5000;
	private $svrCtrl = null;
	
	protected function getServerCtrl() {
		
		if ( $this->svrCtrl === null ) {
			$this->svrCtrl = new ServerCtrl();
		}
	
		return $this->svrCtrl;
	}
	
	protected function channelById($channelid, $functions = null, $checkConnected = false, $authorize = false) {
	
		$channelid = intval($channelid);
		$iodev_man = $this->container->get('iodevice_manager');
	
		$channel = $iodev_man->channelById($channelid, $this->getParentUser());
	
		if ( !($channel instanceof IODeviceChannel ) )
			throw new HttpException(Response::HTTP_NOT_FOUND);
				
		if ( is_array($functions)
					&& !in_array($channel->getFunction(), $functions) )
			throw new HttpException(Response::HTTP_METHOD_NOT_ALLOWED);
	
		if ( $checkConnected === true ) {
			
			$connected = false;
				
			$devid = $channel->getIoDevice()->getId();
			$userid = $this->getParentUser()->getId();
			
			if ( $channel->getIoDevice()->getEnabled() ) {
					
				$enabled = true;
					
				$cids = $this->getServerCtrl()->iodevice_connected($userid, array($devid));
				$connected = in_array($devid, $cids);
			}
			
			if ( $connected === false )
				throw new HttpException(Response::HTTP_SERVICE_UNAVAILABLE);
			
		}
			
		if ( $authorize === true ) {
			
				if ( true !== $this->getServerCtrl()->oauth_authorize($userid,
						$this->container->get('security.token_storage')->getToken()->getToken()) )
					throw new HttpException(Response::HTTP_UNAUTHORIZED);
						
		}
	
		return $channel;
	}
    
	
	protected function getTempHumidityLogCountAction($th, $channelid)
	{
	
		$f = array();
		 
		if ( $th === TRUE ) {
			$f[] = SuplaConst::FNC_HUMIDITYANDTEMPERATURE;
		} else {
			$f[] = SuplaConst::FNC_THERMOMETER;
		}
		
		$channel = $this->channelById($channelid, $f);
	

		$em = $this->container->get('doctrine')->getManager();
		$rep = $em->getRepository('SuplaBundle:'.($th === TRUE ? 'TempHumidityLogItem' : 'TemperatureLogItem'));

		$query = $rep->createQueryBuilder('f')
		->select('COUNT(f.id)')
		->where('f.channel_id = :id')
		->setParameter('id', $channelid)
		->getQuery();

		return $this->handleView($this->view(array('count' => $query->getSingleScalarResult(),
						         'record_limit_per_request' => ChannelController::RECORD_LIMIT_PER_REQUEST),
				                  Response::HTTP_OK));


	}
	
	
	/**
	 * @Rest\Get("/channels/{channelid}/temperature-log-count")
	 */
	public function getTempLogCountAction(Request $request, $channelid)
	{
	
		return $this->getTempHumidityLogCountAction(FALSE, $channelid);
		 
	}
	
	protected function temperatureLogItems($channelid, $offset, $limit) {
	
		$q = $this->container->get('doctrine')->getManager()->getConnection()->query( "SELECT UNIX_TIMESTAMP(`date`) AS date_timestamp, `temperature` FROM `supla_temperature_log` WHERE channel_id = ".intval($channelid, 0)." LIMIT ".$limit." OFFSET ".$offset);
		return $q->fetchAll();
	}
	
	protected function temperatureAndHumidityLogItems($channelid, $offset, $limit) {
	
		$q = $this->container->get('doctrine')->getManager()->getConnection()->query( "SELECT UNIX_TIMESTAMP(`date`) AS date_timestamp, `temperature`, `humidity` FROM `supla_temphumidity_log` WHERE channel_id = ".intval($channelid, 0)." LIMIT ".$limit." OFFSET ".$offset);
		return $q->fetchAll();
	}
	
	
	protected function getTempHumidityLogItemsAction($th, $channelid, $offset, $limit)
	{
	
		$f[] = $th === TRUE ? SuplaConst::FNC_HUMIDITYANDTEMPERATURE : SuplaConst::FNC_THERMOMETER;

		$channel = $this->channelById($channelid, $f);
 
		$offset = intval($offset);
		$limit = intval($limit);

		if ( $limit <= 0 )
			$limit = ChannelController::RECORD_LIMIT_PER_REQUEST;

		if ( $th === TRUE ) {
			$result = $this->temperatureAndHumidityLogItems($channelid, $offset, $limit);
		} else {
			$result = $this->temperatureLogItems($channelid, $offset, $limit);
		}
		 
		return $this->handleView($this->view(array('log' => $result), Response::HTTP_OK));
	
	}
	
	/**
	 * @Rest\Get("/channels/{channelid}/temperature-log-items")
	 */
	public function getTempLogItemsAction(Request $request, $channelid)
	{
		
		return $this->getTempHumidityLogItemsAction(FALSE, $channelid,  @$request->query->get('offset'), @$request->query->get('limit'));	
	}
	
	/**
	 * @Rest\Get("/channels/{channelid}/temperature-and-humidity-count")
	 */
	public function getTempHumLogCountAction(Request $request, $channelid)
	{
		 
		return $this->getTempHumidityLogCountAction(TRUE, $channelid);
	}
	
	/**
	 * @Rest\Get("/channels/{channelid}/temperature-and-humidity-items")
	 */
	public function getTempHumLogItemsAction(Request $request, $channelid)
	{
		 
		return $this->getTempHumidityLogItemsAction(TRUE, $channelid,  @$request->query->get('offset'), @$request->query->get('limit'));	
	}
	
	/**
	 * @Rest\Get("/channels/{channelid}")
	 */
	public function getChannelAction(Request $request, $channelid)
	{
		
		$channel = $this->channelById($channelid);
		
		$enabled = false;
		$connected = false;
		
		$devid = $channel->getIoDevice()->getId();
		$userid = $this->getParentUser()->getId();
		 
		if ( $channel->getIoDevice()->getEnabled() ) {
		
			$enabled = true;
			
			$cids = $this->getServerCtrl()->iodevice_connected($userid, array($devid));
			$connected = in_array($devid, $cids);
		}
		 
		$result = array('connected' => $connected,
				'enabled' => $enabled,
		);
		
		if ( $connected ) {
			
			$func = $channel->getFunction();
			
			switch($func) {
					
				case SuplaConst::FNC_POWERSWITCH:
				case SuplaConst::FNC_LIGHTSWITCH:
					
					$value = $this->getServerCtrl()->get_char_value($userid, $devid, $channelid);
					$result['on'] = $value == '1' ? true : false;
					
				break;
				
				case SuplaConst::FNC_OPENINGSENSOR_GATEWAY:
				case SuplaConst::FNC_OPENINGSENSOR_GATE:
				case SuplaConst::FNC_OPENINGSENSOR_GARAGEDOOR:
				case SuplaConst::FNC_NOLIQUIDSENSOR:
				case SuplaConst::FNC_OPENINGSENSOR_DOOR:
				case SuplaConst::FNC_OPENINGSENSOR_ROLLERSHUTTER:
					
					$value = $this->getServerCtrl()->get_char_value($userid, $devid, $channelid);
					$result['hi'] = $value == '1' ? true : false;
					
				break;
				
				
				case SuplaConst::FNC_THERMOMETER:
				case SuplaConst::FNC_HUMIDITY:
				case SuplaConst::FNC_HUMIDITYANDTEMPERATURE:
					
					if ( $func == SuplaConst::FNC_THERMOMETER
					     || $func == SuplaConst::FNC_HUMIDITYANDTEMPERATURE ) {
					     	
					     	$value = $this->getServerCtrl()->get_temperature_value($userid, $devid, $channelid);
					     	
					     	if ( $value !== FALSE ) {
					     		$result['temperature'] = $value;
					     	}
					}
					
					if ( $func == SuplaConst::FNC_HUMIDITY
					     || $func == SuplaConst::FNC_HUMIDITYANDTEMPERATURE ) {
					     	
					     	$value = $this->getServerCtrl()->get_humidity_value($userid, $devid, $channelid);
					     	
					     	if ( $value !== FALSE ) {
					     		$result['humidity'] = $value;
					     	}
					}
						
				break;
				
				
				case SuplaConst::FNC_DIMMER:
				case SuplaConst::FNC_RGBLIGHTING:
				case SuplaConst::FNC_DIMMERANDRGBLIGHTING:
					
					$value = $this->getServerCtrl()->get_rgbw_value($userid, $devid, $channelid);
					
					if ( $value !== FALSE ) {
							
						if ( $func == SuplaConst::FNC_RGBLIGHTING
							 || $func == SuplaConst::FNC_DIMMERANDRGBLIGHTING ) {
									
							 $result['color'] = $value['color'];
							 $result['color_brightness'] = $value['color_brightness'];
							 	
						}

						if ( $func == SuplaConst::FNC_DIMMER
							 || $func == SuplaConst::FNC_DIMMERANDRGBLIGHTING ) {
										
							$result['brightness'] = $value['brightness'];
										
						}
						
					}
					
				break;
				
				case SuplaConst::FNC_DISTANCESENSOR:
					$value = $this->getServerCtrl()->get_distance_value($userid, $devid, $channelid);
					
					if ( $value !== FALSE ) {
						$result['distance'] = $value;
					}
					
				break;
				
				case SuplaConst::FNC_DEPTHSENSOR:
					$value = $$this->getServerCtrl()->get_distance_value($userid, $devid, $channelid);
						
					if ( $value !== FALSE ) {
						$result['depth'] = $value;
					}
						
				break;
			}
		}
		
		return $this->handleView($this->view($result, Response::HTTP_OK));
		
	}	
	

	
	/**
	 * @Rest\Put("/channels/{channelid}")
	 */
	public function putChannelsAction(Request $request, $channelid)
	{
		$channel = $this->channelById($channelid, null, true, true);
		$data = json_decode($request->getContent());
		
		$devid = $channel->getIoDevice()->getId();
		$userid = $this->getParentUser()->getId();

		$func = $channel->getFunction();

		switch($func) {
			case SuplaConst::FNC_DIMMER:
			case SuplaConst::FNC_RGBLIGHTING:
			case SuplaConst::FNC_DIMMERANDRGBLIGHTING:
				
				$color = intval(@$data->color);
				$color_brightness = intval(@$data->color_brightness);
				$brightness = intval(@$data->brightness);
				
				if ( $func == SuplaConst::FNC_RGBLIGHTING
					 || $func == SuplaConst::FNC_DIMMERANDRGBLIGHTING ) {
				
					if ( $color <= 0
							|| $color > 0xffffff
							|| $color_brightness < 0
							|| $color_brightness > 100 ) {
								
						throw new HttpException(Response::HTTP_BAD_REQUEST);
								
					}
					 	
				}
				
				if ( $func == SuplaConst::FNC_DIMMER
					 || $func == SuplaConst::FNC_DIMMERANDRGBLIGHTING ) {
					 
					if ( $brightness < 0
					 	 || $brightness > 100 ) {
					 	
					 	throw new HttpException(Response::HTTP_BAD_REQUEST);
					 	
					}
					 			
				}
				
				if ( false === $this->getServerCtrl()->set_rgbw_value($userid, $devid, $channelid, $color, $color_brightness, $brightness) )
					throw new HttpException(Response::HTTP_SERVICE_UNAVAILABLE);
				
				break;
				
				default:
					throw new HttpException(Response::HTTP_METHOD_NOT_ALLOWED);
		}
			
	
		return $this->handleView($this->view(NULL, Response::HTTP_OK));
	}
	
	private function patchAllowed($action, $func) {
		
		switch($action) {
			case 'turn-on':
			case 'turn-off':
				switch($func) {
					case SuplaConst::FNC_POWERSWITCH:
					case SuplaConst::FNC_LIGHTSWITCH:
						return true;
				}
				break;
				
			case 'open':
				switch($func) {
					case SuplaConst::FNC_CONTROLLINGTHEGATEWAYLOCK:
					case SuplaConst::FNC_CONTROLLINGTHEDOORLOCK:
						return true;
				}
				break;
				
			case 'open-close':
				switch($func) {
					case SuplaConst::FNC_CONTROLLINGTHEGATE:
					case SuplaConst::FNC_CONTROLLINGTHEGARAGEDOOR:
						return true;
				}
				break;
				
			case 'shut':
			case 'reveal':
			case 'stop':
				
				if ( $func == SuplaConst::FNC_CONTROLLINGTHEROLLERSHUTTER )
					return true;

				break;
				
				
		}
		
		throw new HttpException(Response::HTTP_METHOD_NOT_ALLOWED);
	}
	
	/**
	 * @Rest\Patch("/channels/{channelid}")
	 */
	public function patchChannelsAction(Request $request, $channelid)
	{
		$channel = $this->channelById($channelid, null, true, true);
		$data = json_decode($request->getContent());
		
		$devid = $channel->getIoDevice()->getId();
		$userid = $this->getParentUser()->getId();
		$action = @$data->action;

		$func = $channel->getFunction();
		$this->patchAllowed($action, $func);
		
		$ctrlResult = false;
		
		$value = 0;
		
		switch($action) {
			case 'turn-on':
			case 'open':
			case 'open-close':
			case 'shut':
				$value = 1;
				break;
			case 'reveal':
				$value = 2;
				break;
		}
								
		if ( false === $this->getServerCtrl()->set_char_value($userid, $devid, $channelid, $value) )
			throw new HttpException(Response::HTTP_SERVICE_UNAVAILABLE);
	
		return $this->handleView($this->view(NULL, Response::HTTP_OK));
	}
	
	
	
}

?>