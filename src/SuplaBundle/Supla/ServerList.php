<?php
/*
 src/SuplaBundle/Supla/ServerList.php

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

namespace SuplaBundle\Supla;

use Symfony\Component\HttpFoundation\Request;
use SuplaBundle\Model\UserManager;
use SuplaBundle\Controller\AjaxController;
use Symfony\Bundle\FrameworkBundle\Routing\Router as Router;

class ServerList
{
   protected $server = NULL;
   protected $servers = NULL;
   protected $user_manager = NULL;
   protected $router = NULL;
   
     
   function __construct($router, $user_manager, $supla_server, $supla_server_list) {
   	
   	   $this->router = $router;
       $this->user_manager = $user_manager;
   	   $this->server = $supla_server;
   	   $this->servers = $supla_server_list;
   	   
   	  
   	   if ( count(@$servers) > 1 ) {
   	     	for($a=0;$a<count($servers);$a++)
   	     		if ( $servers[$a]['address'] === $server ) {
   	     			
   	     			if ( $a > 0 ) {
   	     				$s = $servers[$a];
   	     				$servers[$a] = $servers[0];
   	     				$servers[0] = $s;
   	     			}
   	     			
   	     			break;
   	     		}
   	   }
   	   
   }
   

   
   function requestAllowed() {

   	   $addr = @$_SERVER['REMOTE_ADDR'];
   	   
   	   if ( $this->servers != null )
	   	   foreach($this->servers as $server) {
	   	    	if ( @$_SERVER['REMOTE_ADDR'] == $server['ip'] )
	   	  		     return TRUE;
	       };
       
       return FALSE;
   }
  
   
   function userExists($username) {
   	
   	    if ( strlen(@$username) == 0 )
   	    	return false; 
   	
   	    $err = false;
	   	$user = $this->user_manager->userByEmail($username);
	   	 
	   	if ( $user != null ) 
	   		return true;
   
	   	
	   	if ( $this->servers != NULL )
	   		foreach($this->servers as $svr) {
	   			if ( $svr['address'] !== $this->server ) {
	   				  
	   				$rr = AjaxController::remoteRequest('https://'.$svr['address'].$this->router->generate('_account_ajax_user_exists'),
	   						array("username" => $username));
	   				 
	   				if ( $rr != NULL
	   						&& $rr !== FALSE
	   						&& @$rr->success == true ) {
	   	
	   							if ( @$rr->exists == true ) {
	   								return true;
	   							}
	   						} else {
	   							$err = true;
	   						}
	   	
	   			}
	   		}
	   	
	   	
	   	return $err === true ? NULL : false;
	   	
   }
   
   function getAuthServerForUser(Request $request, $username) {
   	
   	     $result = NULL;
   	     $err = false;
   	     
   	     if ( strlen(@$username) > 3 ) {
   	     	
   	     	if ( $this->servers === NULL ) {
   	     		
   	     		$server = $request->getHost();
   	     		
   	     		if ( $request->getPort() != 443 )
   	     			$server .= ":".$request->getPort();
   	     		
   	     		return $server;
   	     	} 	     	
   	     	
   	     	if ( $this->servers != NULL )
   	     		foreach($this->servers as $svr) {
   	     			if ( $svr['address'] === $this->server ) {
   	     				
   	     				
   	     				$user = $this->user_manager->userByEmail($username);
   	     				
   	     				if ( $user != null ) {
   	     					$result = $svr['address'];
   	     					break;
   	     				}
   	     				
   	     			} else {
   	     				
   	     				$rr = AjaxController::remoteRequest('https://'.$svr['address'].$this->router->generate('_account_ajax_user_exists'),
   	     						                       array("username" => $username));
   	     		
   	     				if ( $rr != NULL 
   	     						&& $rr !== FALSE
   	     						&& @$rr->success == true ) {
   	     							
   	     							if ( @$rr->exists == true ) {
   	     								$result = $svr['address'];
   	     								break;
   	     							}
   	     				} else {
   	     					$err = true;
   	     				}
   	     			
   	     			}
   	     		}
   	     	
   	     	
   	     	if ( $err === false
   	     			&& $result === NULL )
   	     				$result = $request->getHost();
   	     	
   	     }

   	  return $result;
   }
   
   
   function getCreateAccountUrl(Request $request) {
   	
   	   if ( count(@$this->servers) < 2 ) {
   	   	  return 'https://'.$request->getHost().$this->router->generate('_account_create_here_lc', array('locale' => $request->getLocale()));
   	   }
   	   
   	   $avil = array();
   	   foreach ($this->servers as $server) {
   	   	  if ( $server['create_account'] === true )
   	   	  	$avil[] = $server;
   	   }
   	   
   	   if ( count($avil) > 0 ) {
   	   	  $server = $avil[rand ( 0 , count($avil)-1 )];
   	   	  
   	   	  if ( strlen(@$server['address']) > 0 )
   	   	  	return 'https://'.$server['address'].$this->router->generate('_account_create_here_lc', array('locale' => $request->getLocale()));
   	   }
   	
   	   return 'https://'.$request->getHost().$this->router->generate('_temp_unavailable');
   }
   
}

?>