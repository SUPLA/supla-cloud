<?php
/*
 src/AppBundle/Entity/IODevice.php

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

namespace AppBundle\Entity;


use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use AppBundle\Entity\IODeviceChannel;

/**
 * @author Przemyslaw Zygmunt p.zygmunt@acsoftware.pl [AC SOFTWARE]
 * @ORM\Entity
 * @ORM\Table(name="supla_iodevice")
 * @UniqueEntity(fields="id", message="IODevice already exists")
 */
class IODevice
{    
	
	/**
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
    /**
     * @ORM\Column(name="guid", type="binary", length=16, nullable=false, unique=true)
     */
    protected $guid;
    
    /**
     * @ORM\Column(name="name", type="string", length=100, nullable=true)
     * @Assert\Length(max=100)
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="Location", inversedBy="ioDevices")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id", nullable=false)
     */
    protected $location;
    
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="iodevices")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;
    
    /**
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     */
    protected $enabled;
    
    /**
     * @ORM\Column(name="comment", type="string", length=200, nullable=true)
     * @Assert\Length(max=200)
     */
    protected $comment;
    
    /**
     * @ORM\Column(name="reg_date", type="datetime")
     * @Assert\NotBlank()
     */
    protected $regDate;
    
    /**
     * @ORM\Column(name="reg_ipv4", type="integer", options={"unsigned"=true})
     */
    protected $regIpv4;
    
    /**
     * @ORM\Column(name="last_connected", type="datetime", nullable=true)
     */
    protected $lastConnected;
    
    /**
     * @ORM\Column(name="last_ipv4", type="integer", nullable=true)
     */
    protected $lastIPv4;
    
    
    /**
     * @ORM\Column(name="software_version", type="string", length=10, nullable=false)
     */
    protected $softwareVersion;
    
    /**
     * @ORM\Column(name="protocol_version", type="integer", nullable=false)
     */
    protected $protocolVersion;
    
    
    public function __construct()
    {
    	
    }
    
    public function getEnabled()
    {
    	return $this->enabled;
    }
    
    public function setEnabled($enabled)
    {
    	$this->enabled = $enabled;
    }
    
    public function getComment() 
    {
    	return $this->comment;
    }
    
    public function setComment($comment) 
    {
    	$this->comment = $comment;
    }
    
    public function getName()
    {
    	return $this->name;
    }
    
    public function getLocation()
    {
    	return $this->location;
    }
    
    public function getRegDate()
    {
    	return $this->regDate;
    }
    
    public function getLastConnected()
    {
    	return $this->lastConnected;
    }
    
    public function getId() 
    {
    	return $this->id;
    }
    
    
    public function getGUID()
    {
        return $this->guid;
    }
    
    public function getGUIDString() 
    {
    	return strtoupper(substr($this->guid, 0, 8).'-'
    			.substr($this->guid, 8, 4).'-'
    			.substr($this->guid, 12, 4).'-'
    			.substr($this->guid, 16, 4).'-'
    			.substr($this->guid, 20, 12));
    }
    
    public function getChannels()
    {
    	return $this->channels;
    }
    
    public function getSoftwareVersion()
    {
    	return $this->softwareVersion;
    }
    
    public function getProtocolVersion()
    {
    	return $this->protocolVersion;
    }
}