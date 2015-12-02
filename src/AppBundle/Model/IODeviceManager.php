<?php
/*
 src/AppBundle/Model/IODeviceManager.php

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

namespace AppBundle\Model;

use AppBundle\Supla\SuplaConst;
use AppBundle\Entity\IODevice;
use AppBundle\Entity\IODeviceChannel;
use AppBundle\Entity\User;

/**
 * @author Przemyslaw Zygmunt p.zygmunt@acsoftware.pl [AC SOFTWARE]
 */
class IODeviceManager 
{	
	protected $translator;
	protected $doctrine;	
	protected $dev_rep;
	protected $channel_rep;
	protected $sec;
	protected $template;
		

	public function __construct($translator, $doctrine, $security_token, $template)
	{
		$this->translator = $translator;
		$this->doctrine = $doctrine;
		$this->dev_rep = $doctrine->getRepository('AppBundle:IODevice');
		$this->channel_rep = $doctrine->getRepository('AppBundle:IODeviceChannel');
		$this->sec = $security_token;
		$this->template = $template;
	}
	
	public function channelFunctionMap($type = null)
	{
		$map[SuplaConst::TYPE_SENSORNO] = array('0', SuplaConst::FNC_OPENINGSENSOR_GATEWAY, 
				                                     SuplaConst::FNC_OPENINGSENSOR_GATE,
				                                     SuplaConst::FNC_OPENINGSENSOR_GARAGEDOOR,
				                                     SuplaConst::FNC_OPENINGSENSOR_DOOR,
				                                     SuplaConst::FNC_NOLIQUIDSENSOR,
				                                     SuplaConst::FNC_OPENINGSENSOR_ROLLERSHUTTER
		);
		$map[SuplaConst::TYPE_RELAYHFD4] = array('0', SuplaConst::FNC_CONTROLLINGTHEGATEWAYLOCK, 
				                                      SuplaConst::FNC_CONTROLLINGTHEGATE, 
				                                      SuplaConst::FNC_CONTROLLINGTHEGARAGEDOOR,
				                                      SuplaConst::FNC_CONTROLLINGTHEDOORLOCK
		);
		
		$map[SuplaConst::TYPE_RELAYG5LA1A] = array('0', SuplaConst::FNC_CONTROLLINGTHEGATEWAYLOCK,
				SuplaConst::FNC_CONTROLLINGTHEGATE,
				SuplaConst::FNC_CONTROLLINGTHEGARAGEDOOR,
				SuplaConst::FNC_CONTROLLINGTHEDOORLOCK,
				SuplaConst::FNC_POWERSWITCH,
				SuplaConst::FNC_LIGHTSWITCH
		);
		$map[SuplaConst::TYPE_2XRELAYG5LA1A] = array('0', SuplaConst::FNC_CONTROLLINGTHEGATEWAYLOCK,
				SuplaConst::FNC_CONTROLLINGTHEGATE,
				SuplaConst::FNC_CONTROLLINGTHEGARAGEDOOR,
				SuplaConst::FNC_CONTROLLINGTHEDOORLOCK,
				SuplaConst::FNC_POWERSWITCH,
				SuplaConst::FNC_LIGHTSWITCH,
				SuplaConst::FNC_CONTROLLINGTHEROLLERSHUTTER
		);
		$map[SuplaConst::TYPE_THERMOMETERDS18B20] = array('0', SuplaConst::FNC_THERMOMETER);
		
		return $type === null ? $map : $map[$type];
	} 
	
	public function channelTypeToString($type) 
	{
		$result = 'Unknown';
		
		switch($type) {
			case SuplaConst::TYPE_SENSORNO: $result = 'Sensor (normal open)'; break;
			case SuplaConst::TYPE_RELAYHFD4: $result = 'HFD4 Relay'; break;
			case SuplaConst::TYPE_RELAYG5LA1A: $result = 'G5LA1A Relay'; break;
			case SuplaConst::TYPE_2XRELAYG5LA1A: $result = 'G5LA1A Relay x2'; break;
			case SuplaConst::TYPE_THERMOMETERDS18B20: $result = 'DS18B20 Thermometer'; break;
		}
		
		return $this->translator->trans($result);		
	}
	
	public function channelFunctionToString($func)
	{
		$result = 'None';
		
		switch($func) {
			case SuplaConst::FNC_CONTROLLINGTHEGATEWAYLOCK:  $result = 'Gateway lock operation'; break;
			case SuplaConst::FNC_CONTROLLINGTHEGATE:  $result = 'Gate operation'; break;
			case SuplaConst::FNC_CONTROLLINGTHEGARAGEDOOR:  $result = 'Garage door operation'; break;
			case SuplaConst::FNC_THERMOMETER:  $result = 'Thermometer'; break;
			case SuplaConst::FNC_OPENINGSENSOR_GATEWAY:  $result = 'Gateway opening sensor'; break;
			case SuplaConst::FNC_OPENINGSENSOR_GATE:  $result = 'Gate opening sensor'; break;
			case SuplaConst::FNC_OPENINGSENSOR_GARAGEDOOR:  $result = 'Garage door opening sensor'; break;
			case SuplaConst::FNC_NOLIQUIDSENSOR: $result = 'No liquid sensor'; break;
			case SuplaConst::FNC_CONTROLLINGTHEDOORLOCK:  $result = 'Door lock operation'; break;
			case SuplaConst::FNC_OPENINGSENSOR_DOOR:  $result = 'Door opening sensor'; break;
			case SuplaConst::FNC_CONTROLLINGTHEROLLERSHUTTER:  $result = 'Roller shutter operation'; break;
			case SuplaConst::FNC_OPENINGSENSOR_ROLLERSHUTTER:  $result = 'Roller shutter opening sensor'; break;
			case SuplaConst::FNC_POWERSWITCH:  $result = 'On/Off switch'; break;
			case SuplaConst::FNC_LIGHTSWITCH:  $result = 'Light switch'; break;
		}
		
		return $this->translator->trans($result);
	}
	
	public function FunctionIsOpeningSensor($func) {
		
		switch($func) {
			case SuplaConst::FNC_OPENINGSENSOR_GATEWAY: 
			case SuplaConst::FNC_OPENINGSENSOR_GATE: 
			case SuplaConst::FNC_OPENINGSENSOR_GARAGEDOOR:  
			case SuplaConst::FNC_OPENINGSENSOR_DOOR: 
			case SuplaConst::FNC_OPENINGSENSOR_ROLLERSHUTTER:
				return true;
		}
		
		return false;
	}
	 
	public function channelIoToString($type) 
	{
		$result = 'Unknown';
		
		switch($type) {
			case SuplaConst::TYPE_THERMOMETERDS18B20: 
			case SuplaConst::TYPE_SENSORNO: $result = 'Input'; break;
			case SuplaConst::TYPE_RELAYG5LA1A:
			case SuplaConst::TYPE_2XRELAYG5LA1A:
			case SuplaConst::TYPE_RELAYHFD4: $result = 'Output'; break;
			
		}
		
		return $this->translator->trans($result);
	}
	
	public function ioDeviceById($id) 
	{
		$user = $this->sec->getToken()->getUser();
		
		if ( $user === null ) 
			return null;
		
		return $this->dev_rep->findOneBy(array('user' => $user, 'id' => intval($id)));
	}
	
	public function channelById($id) 
	{
		$user = $this->sec->getToken()->getUser();
		
		if ( $user === null ) 
			return null;
		
		return $this->channel_rep->findOneBy(array('user' => $user, 'id' => intval($id)));
	}
	
	public function getChannels(IODevice $iodev) 
	{
		if ( $iodev === null || !($iodev instanceof IODevice) )
			return null;
		
		return $this->channel_rep->findBy(
				array('iodevice' => $iodev)
		);
	}
	
	public function getUnattachedOPENINGSENSORs($func, $include = 0)
	{
		$user = $this->sec->getToken()->getUser();
		
		return $this->channel_rep->findBy(
				array('user' => $user, 
					  'type' => SuplaConst::TYPE_SENSORNO, 
					  'function' => $func, 
					  'param1' => array(0, $include), 
					  'param2' => 0, 
					  'param3' => 0)
		);
	}
	
	public function getSensorUnnattachedSubChannels($func, $include = 0) {
		
		$user = $this->sec->getToken()->getUser();
		
		return $this->channel_rep->findBy(
				array('user' => $user, 
					  'type' => array(SuplaConst::TYPE_RELAYHFD4, SuplaConst::TYPE_RELAYG5LA1A, SuplaConst::TYPE_2XRELAYG5LA1A),
					  'function' => $func, 
					  'param2' => array(0, $include), 
					  'param3' => 0)
		);
		
	}
	
	public function channelsToArray($channels) 
	{
		$result = array();

		foreach($channels as $channel) {
			
			$item = array('id' => $channel->getId(),
					      'number' => $channel->getChannelNumber(),
					      'io' => $this->channelIoToString($channel->getType()),
					      'type' => $this->channelTypeToString($channel->getType()),
					      'function' => $this->channelFunctionToString($channel->getFunction()),
					      'function_id' => $channel->getFunction(),
					      'function_is_openingsensor' => $this->FunctionIsOpeningSensor($channel->getFunction()),
					      'caption' => $channel->getCaption()
			);
			
			$result[$channel->getId()] = $item;
		}

		
		return $result;
	}
	
	public function getChannelName(IODeviceChannel $channel)
	{
		$result = substr($channel->getIoDevice()->getGUIDString(), -12);
		
		if ( strlen($channel->getIoDevice()->getName()) > 0 ) {
			$result .= ' ['.trim(substr($channel->getIoDevice()->getName(), 0, 50)).']';
		}
		
		$result .= ' '.$this->channelFunctionToString($channel->getFunction()).' #'.$channel->getChannelNumber();
		
		if ( strlen($channel->getCaption()) > 0 ) {
			$result .= ' ['.substr($channel->getCaption(), 0, 50).']';
		}
		
		return $result;
	}
	
	private function channelsToTwigParams($_channels)
	{
		$result = array();
			
		if ( is_array($_channels) === true )
			foreach($_channels as $channel) {
				$result[] = array('id'=>$channel->getId(), 'name'=>$this->getChannelName($channel));
			}
			
		return array('channels' => $result);
	}
	
	public function channelFunctionParamsHtmlTemplate($channel_id, $function = null) 
	{
		
		$channel = null;
		$cinstance = false;
		
		if ( $channel_id instanceof IODeviceChannel) {
			
			$channel = $channel_id;
			$function = $channel->getFunction();
			$cinstance = true;
			
		} else {
			$function = intval($function);
			$channel = $this->channelById($channel_id);
		}
		
	
		if ( $channel ) {
			
			$tmpl = 'none';
			$subchannel_selected = null;
			$twig_params = array('channel' => $channel);
			
			switch($function) {
				

				
				case SuplaConst::FNC_CONTROLLINGTHEGATEWAYLOCK: 
				case SuplaConst::FNC_CONTROLLINGTHEGATE: 
				case SuplaConst::FNC_CONTROLLINGTHEGARAGEDOOR: 
			    case SuplaConst::FNC_CONTROLLINGTHEDOORLOCK:
						
					$timesel[] = array('name'=>'0.5', 'val' => '500');
					$timesel[] = array('name'=>'1', 'val' => '1000');
					$timesel[] = array('name'=>'2', 'val' => '2000');
					
					
					if ( $function == SuplaConst::FNC_CONTROLLINGTHEGATEWAYLOCK
						 || $function == SuplaConst::FNC_CONTROLLINGTHEDOORLOCK ) {
						 	
						$timesel[] = array('name'=>'4', 'val' => '4000');
						$timesel[] = array('name'=>'6', 'val' => '6000');
						$timesel[] = array('name'=>'8', 'val' => '8000');
						$timesel[] = array('name'=>'10', 'val' => '10000');
						
						$twig_params['default_time_val'] = '6000';
						
					} else {
						$twig_params['default_time_val'] = '500';
					}
					
					
					$os_func = 0;
					
					switch($function) {
					
						case SuplaConst::FNC_CONTROLLINGTHEGATEWAYLOCK:
							$os_func = SuplaConst::FNC_OPENINGSENSOR_GATEWAY;
							break;
							
						case SuplaConst::FNC_CONTROLLINGTHEGATE:
							$os_func = SuplaConst::FNC_OPENINGSENSOR_GATE;
							break;
							
						case SuplaConst::FNC_CONTROLLINGTHEGARAGEDOOR:
							$os_func = SuplaConst::FNC_OPENINGSENSOR_GARAGEDOOR;
							break;
							
						case SuplaConst::FNC_CONTROLLINGTHEDOORLOCK:
							$os_func = SuplaConst::FNC_OPENINGSENSOR_DOOR;
							break;
										
						case SuplaConst::FNC_CONTROLLINGTHEROLLERSHUTTER:
							$os_func = SuplaConst::FNC_OPENINGSENSOR_ROLLERSHUTTER;
							break;
					};
					
					$tmpl = 'gateway';			
					$twig_params = array_merge($twig_params, $this->channelsToTwigParams($this->getUnattachedOPENINGSENSORs($os_func, $channel->getId())));
					$twig_params['subchannel_selected'] = $this->translator->trans('None');
					$twig_params['timesel'] = $timesel;
					$twig_params['cinstance'] = $cinstance;
					$subchannel_selected = $channel->getParam2();
					
					
					break;
					
					
				case SuplaConst::FNC_CONTROLLINGTHEROLLERSHUTTER:
							

					$tmpl = 'rollershutter';
					$twig_params = array_merge($twig_params, $this->channelsToTwigParams($this->getUnattachedOPENINGSENSORs(SuplaConst::FNC_OPENINGSENSOR_ROLLERSHUTTER, $channel->getId())));
					$twig_params['subchannel_selected'] = $this->translator->trans('None');
					$subchannel_selected = $channel->getParam2();
				
					break;
					
				case SuplaConst::FNC_OPENINGSENSOR_GATEWAY:
				case SuplaConst::FNC_OPENINGSENSOR_GATE:
				case SuplaConst::FNC_OPENINGSENSOR_GARAGEDOOR:
				case SuplaConst::FNC_OPENINGSENSOR_DOOR:
				case SuplaConst::FNC_OPENINGSENSOR_ROLLERSHUTTER:
					
					$sc_func = 0;
					
					switch($function) {
						case SuplaConst::FNC_OPENINGSENSOR_GATEWAY:
							$sc_func = SuplaConst::FNC_CONTROLLINGTHEGATEWAYLOCK;
							break;
							
						case SuplaConst::FNC_OPENINGSENSOR_GATE:
							$sc_func = SuplaConst::FNC_CONTROLLINGTHEGATE;
							break;
							
						case SuplaConst::FNC_OPENINGSENSOR_GARAGEDOOR:
							$sc_func = SuplaConst::FNC_CONTROLLINGTHEGARAGEDOOR;
							break;
		
						case SuplaConst::FNC_OPENINGSENSOR_DOOR:
							$sc_func = SuplaConst::FNC_CONTROLLINGTHEDOORLOCK;
							break;
							
						case SuplaConst::FNC_OPENINGSENSOR_ROLLERSHUTTER:
							$sc_func = SuplaConst::FNC_CONTROLLINGTHEROLLERSHUTTER;
							break;
					};
					
					$tmpl = 'openingsensor';
					$twig_params = array_merge($twig_params, $this->channelsToTwigParams($this->getSensorUnnattachedSubChannels($sc_func, $channel->getId())));
					$twig_params['subchannel_selected'] = $this->translator->trans('None');
					$subchannel_selected = $channel->getParam1();
										
					break;

			}
			
			if ( is_int ($subchannel_selected) === true
					&& $subchannel_selected !== 0
					&& $cinstance === true ) {
						
						$subchannel = $this->channelById($subchannel_selected);
						
						if ( $subchannel !== null )
							$twig_params['subchannel_selected'] = $this->getChannelName($subchannel);
			
			}
			
			return $this->template->render('AppBundle:Form:ChannelFunctions/'.$tmpl.'.html.twig', $twig_params);
			
		}
		
		return null;
	}
	
	
	
}
