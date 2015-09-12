<?php
/*
 src/AppBundle/Controller/IODeviceController.php

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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Supla\SuplaConst;
use AppBundle\Entity\IODevice;
use AppBundle\Form\Type\IODeviceType;
use AppBundle\Form\Type\IODeviceChannelType;
use AppBundle\Supla\ServerCtrl;

/**
 * @author Przemyslaw Zygmunt p.zygmunt@acsoftware.pl [AC SOFTWARE]
 * @Route("/iodev")
 */
class IODeviceController extends Controller
{
	
	private function user_reconnect() {
		$user = $this->get('security.context')->getToken()->getUser();
		(new ServerCtrl())->reconnect($user->getId());
	}
	
	private function getIODeviceById($id)
	{
		$iodev_man = $this->get('iodevice_manager');
		return $iodev_man->ioDeviceById($id);
	}
	
	private function getChannelById($id)
	{
		$iodev_man = $this->get('iodevice_manager');
		return $iodev_man->channelById($id);
	}
	
    /**
     * @Route("/", name="_iodev_list")
     */
    public function listAction()
    {
    	$user = $this->get('security.context')->getToken()->getUser();
    	return $this->render('AppBundle:IODevice:list.html.twig', array('iodevices' => $user->getIODevices()));
    }
  
    
    /**
     * @Route("/{id}/view", name="_iodev_item")
     */
    public function itemAction($id)
    {
    
    	$iodev = $this->getIODeviceById($id);
    	 
    	if ( $iodev === null )
    		return $this->redirectToRoute("_iodev_list");
    
    	$dev_man = $this->get('iodevice_manager');
    	
    	$loc = $iodev->getLocation();
    	
    	$loc_name = 'Id '.$loc->getId();
    	
    	if ( strlen($loc->getCaption()) > 0 ) 
    		$loc_name.=" [".$loc->getCaption()."]";
    	
    	$aid_enabled = false;

    	for($a=0;$a<$iodev->getLocation()->getAccessIds()->count();$a++) {
    		$aid = $iodev->getLocation()->getAccessIds()->get($a);
    		if ( $aid->getEnabled() ) {
    			$aid_enabled = true;
    			break;
    		}
    	}
    
    	return $this->render('AppBundle:IODevice:iodevice.html.twig',
    			array('iodevice' => $iodev,
    				  'location_name' => $loc_name,
    				  'channels' => $dev_man->channelsToArray($dev_man->getChannels($iodev)),
    				  'aid_enabled' => $aid_enabled
    			)
    			);
       
    }
    
    /**
     * @Route("/{id}/edit", name="_iodev_item_edit")
     */
    public function itemEditAction(Request $request, $id)
    {
    	/*
    	$iodev = $this->getIODeviceById($id);
    
    	if ( $iodev === null )
    		return $this->redirectToRoute("_iodev_list");
    
    	 
    	$form = $this->createForm(new IODeviceType(),
    			$iodev,
    			array('cancel_url'=>$this->generateUrl('_iodev_item', array('id' => $iodev->getId()))));
    	
    	$form->handleRequest($request);
    	 
    	if ($form->isSubmitted() && $form->isValid()) {
    			
    		$this->get('doctrine')->getManager()->flush();
    		$this->get('session')->getFlashBag()->add('success', array('title' => 'Success', 'message' => 'Data saved!'));
    			
    		return $this->redirectToRoute("_iodev_item", array('id' => $iodev->getId()));
    	}
    	 
    	return $this->render('AppBundle:IODevice:iodevedit.html.twig',
    			array('form' => $form->createView(),
    					'iodevice' => $iodev
    			));
    			*/
    }
 
    
    /**
     * @Route("/{id}/delete", name="_iodev_item_delete")
     */
    public function itemDeleteAction($id)
    {
    	$iodev = $this->getIODeviceById($id);
    
    	if ( $iodev === null )
    		return $this->redirectToRoute("_iodev_list");
    	
    	$dev_man = $this->get('iodevice_manager');  	 
    	$m = $this->get('doctrine')->getManager();
    	
    	$channels = $dev_man->getChannels($iodev);
    	
    	//TODO: !!!wyczyscic powiązania kanałów z innymi urzadzeniami!!!
    	
    	foreach ($channels as $channel) {
    		$m->remove($channel);
    	}
    	
    	$m->remove($iodev);
    	$m->flush();
    	
    	$this->user_reconnect();
    	 
    	$this->get('session')->getFlashBag()->add('warning', array('title' => 'Information', 'message' => 'I/O Device has been deleted'));
    	return $this->redirectToRoute("_iodev_list");
    }
    
    /**
     * @Route("/{devid}/{id}/edit", name="_iodev_channel_item_edit")
     */
    public function channelItemEditAction(Request $request, $devid, $id)
    {

    	$channel = $this->getChannelById($id);
    	
    	if ( $channel === null || $channel->getIoDevice()->getId() != $devid )
    		return $this->redirectToRoute("_iodev_list");
    	
    	$dev_man = $this->get('iodevice_manager');
    	
    	$form = $this->createForm(new IODeviceChannelType(),
    			$channel,
    			array('cancel_url'=>$this->generateUrl('_iodev_item', array('id' => $devid))));
    	
    	$old_function = $channel->getFunction();
    	$old_param1 = $channel->getParam1();
    	$old_param2 = $channel->getParam2();
    	
    	$form->handleRequest($request);
    	
    	if ($form->isSubmitted() && $form->isValid()) {
    		    		
    		switch($channel->getType()) {
    			
    			case SuplaConst::TYPE_SENSORNO:
    				
    				if (  $channel->getFunction() == SuplaConst::FNC_NONE
    						|| $old_param1 != $channel->getParam1() ) {
    				
    							if ( $old_param1 != 0 ) {
    								
    								$old_channel = $this->getChannelById($old_param1);
    								
    								if ( $old_channel != null
    									 && $old_channel->getFunction() != SuplaConst::FNC_NONE
    									 && $old_channel->getParam2() == $channel->getId() )
    									
    								$old_channel->setParam2(0);
    								
    							}
    							 
    						};
    				
    				if ( ( $channel->getFunction() == SuplaConst::FNC_OPENSENSOR_GATEWAY
    						|| $channel->getFunction() == SuplaConst::FNC_OPENSENSOR_GATE
    						|| $channel->getFunction() == SuplaConst::FNC_OPENSENSOR_GARAGEDOOR )
    					 && $old_param1 != $channel->getParam1()
						 && $channel->getParam1() != SuplaConst::FNC_NONE  ) {
						 	
						 	$new_channel = $this->getChannelById($channel->getParam1());
						 	
						 	if ( $new_channel != null 
						 		 && $new_channel->getFunction() != SuplaConst::FNC_NONE )
						 			
						 		$new_channel->setParam2($channel->getId());
						 	
						 }
    						
    				break;
    				
				case SuplaConst::TYPE_RELAYHDF4:

					
					if (  $channel->getFunction() == SuplaConst::FNC_NONE
						  || $old_param2 != $channel->getParam2() ) {
					      	
					      	if ( $old_param2 != 0 ) {
					      		$old_sensor = $this->getChannelById($old_param2);
					      		$old_sensor->setParam1(0);
					      	}
					      	  	
					};
						
					if ( $channel->getFunction() != SuplaConst::FNC_NONE
						 && $old_param2 != $channel->getParam2()
						 && $channel->getParam2() != SuplaConst::FNC_NONE ) {
				
						 	$new_sensor = $this->getChannelById($channel->getParam2());
						 	$new_sensor->setParam1($channel->getId());
					}
	
					break;
				

    		}

	
    		$this->get('doctrine')->getManager()->flush();
    		$this->get('session')->getFlashBag()->add('success', array('title' => 'Success', 'message' => 'I/O Device Channel data saved'));
    		 
    		$this->user_reconnect();
    		
    		return $this->redirectToRoute("_iodev_item", array('id' => $devid));
    	}
    	
   
    	return $this->render('AppBundle:IODevice:channeledit.html.twig',
    			array('channel' => $channel,
    				  'channel_type' => $dev_man->channelTypeToString($channel->getType()),
    				  'form' => $form->createView(),
    				  'show_sensorstate' => $channel->getType() == SuplaConst::TYPE_SENSORNO ? true : false,
    				  'show_temperature' => $channel->getType() == SuplaConst::TYPE_THERMOMETERDS18B20 ? true : false,
    			)
    	);
    	
    	 
    }
    
    
    private function ajaxItemEdit(IODevice $iodev, $message, $value)
    {
    	$result = AjaxController::itemEdit($this->get('validator'), $this->get('translator'), $this->get('doctrine'), $iodev, $message, $value);
    	$this->user_reconnect();
    	return $result;
    }

      
    /**
     * @Route("/{id}/ajax/setenabled/{enabled}", name="_iodev_ajax_setenabled")
     */
    public function ajaxSetEnabled(Request $request, $id, $enabled)
    {
    	$iodev = $this->getIODeviceById($id);
    
    	if ( $iodev !== null )
    		$iodev->setEnabled($enabled == '1');
    	 
    	return $this->ajaxItemEdit($iodev,
    			$this->get('translator')->trans('I/O Device has been '.($enabled == '1' ? 'enabled' : 'disabled')),
    			$this->get('translator')->trans($enabled == '1' ? 'Enabled' : 'Disabled')
    	);
    	 
    }
    
    
    /**
     * @Route("/{id}/ajax/setcomment", name="_iodev_ajax_setcomment")
     */
    public function ajaxSetCaption(Request $request, $id)
    {
    	$iodev = $this->getIODeviceById($id);
    
    	if ( $iodev !== null ) {
    		$data = json_decode($request->getContent());
    		$iodev->setComment($data->value);
    	}
    
    	return $this->ajaxItemEdit($iodev,
    			$this->get('translator')->trans('Comment has been changed'),
    			null
    	);
    }
    
    /**
     * @Route("/ajax/getfuncparams/{channel_id}/{function}", name="_iodev_ajax_getfuncparams")
     */
    public function ajaxGetfuncparamsAction($channel_id, $function)
    {
    
    	$dev_man = $this->get('iodevice_manager');
    	$html = $dev_man->channelFunctionParamsHtmlTemplate($channel_id, $function);
    
    	return AjaxController::jsonResponse($html !== null, array('html'=>$html));
    }
    

}
