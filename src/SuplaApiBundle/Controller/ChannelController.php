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
use SuplaBundle\Entity\IODeviceChannel;

class ChannelController extends RestController
{
	
	const RECORD_LIMIT_PER_REQUEST     = 5000;
	
	protected function channelById($channelid, $functions = null) {
	
		$channelid = intval($channelid, 0);
		$iodev_man = $this->container->get('iodevice_manager');
	
		$channel = $iodev_man->channelById($channelid, $this->getParentUser());
	
		if ( !($channel instanceof IODeviceChannel ) )
			throw new HttpException(Response::HTTP_NOT_FOUND);
				
			if ( is_array($functions)
					&& !in_array($channel->getFunction(), $functions) )
				throw new HttpException(Response::HTTP_METHOD_NOT_ALLOWED);
	
	
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
	
	
	protected function getLogTempHumidityItemsAction($th, $channelid, $offset, $limit)
	{
	
		$f[] = $th === TRUE ? SuplaConst::FNC_HUMIDITYANDTEMPERATURE : SuplaConst::FNC_THERMOMETER;

		$channel = $this->channelById($channelid, $f);
 
		$offset = intval($offset, 0);
		$limit = intval($limit, 0);

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
	public function getLogTempItemsAction(Request $request, $channelid)
	{
		
		return $this->getLogTempHumidityItemsAction(FALSE, $channelid,  @$request->query->get('offset'), @$request->query->get('limit'));	
	}
	
	/**
	 * @Rest\Get("/channels/{channelid}/temperature-and-humidity-count")
	 */
	public function getLogTempHumCountAction(Request $request, $channelid)
	{
		 
		return $this->getTempHumidityLogCountAction(TRUE, $channelid);
	}
	
	/**
	 * @Rest\Get("/channels/{channelid}/temperature-and-humidity-items")
	 */
	public function getLogTempHumItemsAction(Request $request, $channelid)
	{
		 
		return $this->getLogTempHumidityItemsAction(TRUE, $channelid,  @$request->query->get('offset'), @$request->query->get('limit'));	
	}
	
}

?>