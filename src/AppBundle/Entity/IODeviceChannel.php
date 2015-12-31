<?php
/*
 src/AppBundle/Entity/IODeviceChannel.php

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
use AppBundle\Validator\Constraints as SuplaAssert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

/**
 * @author Przemyslaw Zygmunt p.zygmunt@acsoftware.pl [AC SOFTWARE]
 * @ORM\Entity
 * @ORM\Table(name="supla_dev_channel", uniqueConstraints={@ORM\UniqueConstraint(name="UNIQUE_CHANNEL", columns={"iodevice_id","channel_number"})})
 * @SuplaAssert\Channel
 */
class IODeviceChannel
{    
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(name="channel_number", type="integer", nullable=false)
     */
    protected $channelNumber;
    
    /**
     * @ORM\ManyToOne(targetEntity="IODevice")
     * @ORM\JoinColumn(name="iodevice_id", referencedColumnName="id", nullable=false)
     */
    protected $iodevice;
    
    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;
    
    /**
     * @ORM\Column(name="caption", type="string", length=100, nullable=true)
     * @Assert\Length(max=100)
     */
    protected $caption;   

    /**
     * @ORM\Column(name="type", type="integer", nullable=false)
     */
    protected $type;
    
    /**
     * @ORM\Column(name="func", type="integer", nullable=false)
     */
    protected $function; 
    
    
    /**
     * @ORM\Column(name="flist", type="integer", nullable=true)
     */
    protected $funcList;
    
    /**
     * @ORM\Column(name="param1", type="integer", nullable=false)
     */
    protected $param1;
    
    
    /**
     * @ORM\Column(name="param2", type="integer", nullable=false)
     */
    protected $param2;
    
    /**
     * @ORM\Column(name="param3", type="integer", nullable=false)
     */
    protected $param3;
    
    public function getId() 
    {
    	return $this->id;
    }
    
    public function getChannelNumber()
    {
    	return $this->channelNumber;
    }
    
    public function getCaption() 
    {
    	return $this->caption;
    }
    
    public function setCaption($caption)
    {
    	$this->caption = $caption;
    }
    
    public function getType()
    {
    	return $this->type;
    }

    public function getIoDevice() 
    {
    	return $this->iodevice;
    }
    
    public function getFunction()
    {
    	return $this->function;
    }
    
    public function setFunction($function) 
    {
    	$this->function = $function;
    }
    
    public function getFuncList()
    {
    	return $this->funcList;
    }
    
    public function setFuncList($funcList)
    {
    	$this->funcList = $funcList;
    }
    
    public function getChannel() 
    {
    	return $this;
    }
    
    public function getParam1()
    {
    	return $this->param1;
    }    
    
    public function setParam1($param1)
    {
    	$this->param1 = $param1;
    }
    
    public function getParam2()
    {
    	return $this->param2;
    }
    
    public function setParam2($param2)
    {
    	$this->param2 = $param2;
    }
    
    public function getParam3()
    {
    	return $this->param3;
    }
    
    public function setParam3($param3)
    {
    	$this->param3 = $param3;
    }
}