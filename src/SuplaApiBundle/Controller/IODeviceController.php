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
use SuplaBundle\Supla\SuplaConst;
use SuplaBundle\Supla\ServerCtrl;

class IODeviceController extends RestController
{
	
	protected function getIODevices() {
	
		$result = array();
		$parent = $this->getParentUser();
			
		if ( $parent !== null ) {
			
			$iodev_man = $this->container->get('iodevice_manager');
			
			foreach($parent->getIODevices() as $device) {
			
				$channels = array();
			
				foreach($iodev_man->getChannels($device) as $channel) {
					$channels[] = array(
							'id' => $channel->getId(),
							'chnnel_number' => $channel->getChannelNumber(),
							'caption' => $channel->getCaption(),
							'type' => array('name' => SuplaConst::typeStr[$channel->getType()],
									'id' => $channel->getType()),
							'function' => array('name' => SuplaConst::fncStr[$channel->getFunction()],
									'id' => $channel->getFunction()),
					);
				}
			
				$result[] = array(
						'id' => $device->getId(),
						'location_id' => $device->getLocation()->getId(),
						'enabled' => $device->getEnabled(),
						'name' => $device->getName(),
						'comment' => $device->getComment(),
						'registration' => array('date' => $device->getRegDate()->getTimestamp(),
								'ip_v4' => long2ip($device->getRegIpv4())),
			
						'last_connected' => array('date' => $device->getLastConnected()->getTimestamp(),
								'ip_v4' => long2ip($device->getLastIpv4())),
						'guid' => $device->getGUIDString(),
						'software_version' => $device->getSoftwareVersion(),
						'protocol_version' => $device->getProtocolVersion(),
						'channels' => $channels,
				);
			}
			
		}

			
		return array('iodevices' => $result);
	}
	
    /**
     * @Rest\Get("/iodevices")
     */
    public function getIOdevicesAction(Request $request)
    {
    	return  $this->handleView($this->view($this->getIODevices(), Response::HTTP_OK));
    }
    
    
    /**
     * @Rest\Get("/iodevices/{devid}")
     */
    public function getIOdeviceAction(Request $request, $devid)
    {
    	$iodevice = $this->getApiManager()->ioDeviceById($devid, $this->getApiUser());
    	
    	$cids = (new ServerCtrl())->iodevice_connected($this->getParentUser()->getId(), array($devid));
    	
    	$result = array('connected' => in_array($devid, $cids),
    			        'enabled' => $iodevice->getEnabled() ? true : false,
    	);
    	
    	return  $this->handleView($this->view($result, Response::HTTP_OK));
    }
    
	
}

?>