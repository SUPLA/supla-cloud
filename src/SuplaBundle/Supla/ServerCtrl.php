<?php
/*
 src/SuplaBundle/Supla/ServerCtrl.php

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


class ServerCtrl
{
   private $socket = FALSE;
   
   
   private function connect() {
   
   	if ( $this->socket !== FALSE )
   		return $this->socket;
   
    $old_er = error_reporting();
    error_reporting($old_er ^ E_WARNING);
   	$this->socket = stream_socket_client('unix:///tmp/supla-server-ctrl.sock', $errno, $errstr);
   	error_reporting($old_er);
   	
   	if ( $this->socket === FALSE || $this->socket === null ) {
   		$this->socket == FALSE;
   		return FALSE;
   	}
   		
   	$hello = fread($this->socket, 4096);
   	
   	if ( preg_match("/^SUPLA SERVER CTRL\n/", $hello) !== 1  ) {
   		$this->disconnect();
   	}
   	 
   	
   			
   	return $this->socket;
   
   }
   
   private function disconnect() {
   	
   	if ( $this->socket !== FALSE ) {
   		fclose($this->socket);
   		$this->socket = FALSE;
   	}
   	
   }
   
   private function command($cmd) {
   	
   	if ( $this->socket !== FALSE ) {
   		fwrite($this->socket,  $cmd."\n");   		
   		$result = fread($this->socket, 4096);
   		$this->disconnect();
   		
   		return $result;
   	}
   	
   	return FALSE;
   	
   }
   
   private function _iodevice_connected($user_id, $iodev_id) {
   
   	$iodev_id = intval($iodev_id, 0);
   	 
   	if ( $user_id == 0 || $iodev_id == 0 )
   		return FALSE;
   
   	$result = $this->command("IS-IODEV-CONNECTED:".$user_id.",".$iodev_id);
   	 
   	return $result !== FALSE && preg_match("/^CONNECTED:".$iodev_id."\n/", $result) === 1 ? TRUE : FALSE;
   }
   
  
   function iodevice_connected($user_id, $ids = 0) {

   	$result = array();
   	$user_id = intval($user_id, 0);
  
   	
   	if ( $user_id != 0 && $this->connect() !== FALSE ) {
   		
   		if ( !is_array($ids) && is_int($ids) ) {
   			$ids = array($ids);
   		}
   		
   		if ( is_array($ids) )
   			foreach($ids as $id) {
   				if ( $this->_iodevice_connected($user_id, $id) === TRUE )
   					$result[] = $id;
   			}
   		
   		$this->disconnect();
   		
   	}
   	

   	return $result;
   	
   }

   function reconnect($user_id) {
   	 
   	$user_id = intval($user_id, 0);
   	 
   	if ( $user_id != 0 && $this->connect() !== FALSE ) {
   		$result = $this->command("USER-RECONNECT:".$user_id);
   		return $result !== FALSE && preg_match("/^OK:".$user_id."\n/", $result) === 1 ? TRUE : FALSE;
   	}
   	 
   	return FALSE;
   }
   
   
   private function get_value($type, $user_id, $iodev_id, $channel_id) {
   
   	$user_id = intval($user_id, 0);
   	$iodev_id = intval($iodev_id, 0);
   	$channel_id = intval($channel_id, 0);
   	
   	 
   	if ( $user_id != 0
   			&& $iodev_id != 0
   			&& $channel_id != 0
   			&& $this->connect() !== FALSE ) {
   					
   				$result = $this->command("GET-".$type."-VALUE:".$user_id.",".$iodev_id.",".$channel_id);

   				
   				if ( $result !== FALSE
   						&& preg_match("/^VALUE:/", $result) === 1 ) {
   							list($val) = sscanf($result, "VALUE:%f\n");
   
   							if ( is_numeric($val) ) {
   								return $val;
   							};
   						}
   						 
   			}
   			 
   			return FALSE;
   
   }
    
   function get_char_value($user_id, $iodev_id, $channel_id) {
   	return $this->get_value('CHAR', $user_id, $iodev_id, $channel_id);
   }
   
   function get_double_value($user_id, $iodev_id, $channel_id) {
   	    return $this->get_value('DOUBLE', $user_id, $iodev_id, $channel_id);
   }
   
   function get_temperature_value($user_id, $iodev_id, $channel_id) {
   	    return $this->get_value('TEMPERATURE', $user_id, $iodev_id, $channel_id);
   }
   
   function get_humidity_value($user_id, $iodev_id, $channel_id) {
   	return $this->get_value('HUMIDITY', $user_id, $iodev_id, $channel_id);
   }
   
   function get_distance_value($user_id, $iodev_id, $channel_id) {
      return $this->get_value('DOUBLE', $user_id, $iodev_id, $channel_id);
   }
   
}

?>