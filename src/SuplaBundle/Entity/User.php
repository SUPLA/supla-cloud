<?php
/*
 src/SuplaBundle/Entity/User.php

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

namespace SuplaBundle\Entity;


use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @UniqueEntity(fields="email", message="Email already exists")
 * @ORM\Table(name="supla_user")
 */
class User implements AdvancedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="salt", type="string", length=32)
     */
    private $salt;

    /**
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     * @Assert\Length(max=255)
     */
    private $email;

    /**
     * @ORM\Column(name="password", type="string", length=64)
     */
    private $password;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     * @Assert\NotBlank()
     * @Assert\Length(min=8)
     */
    private $plainPassword;

    /**
     * @ORM\Column(name="enabled", type="boolean")
     * @Assert\NotNull()
     */
    private $enabled;

    /**
     * @ORM\Column(name="reg_date", type="datetime")
     * @Assert\NotBlank()
     */
    private $regDate;

    /**
     * @ORM\Column(name="last_login", type="datetime", nullable=true)
     */
    private $lastLogin;

    /**
     * @ORM\Column(name="last_ipv4", type="integer", nullable=true)
     */
    private $lastIpv4;

    /**
     * @ORM\Column(name="current_login", type="datetime", nullable=true)
     */
    private $currentLogin;

    /**
     * @ORM\Column(name="current_ipv4", type="integer", nullable=true)
     */
    private $currentIpv4;

    /**
     * @ORM\Column(name="token", type="string")
     */
    private $token;

    /**
     * @ORM\Column(name="password_requested_at", type="datetime", nullable=true)
     */
    private $passwordRequestedAt;


    private $recaptcha;

    /**
     * @ORM\Column(name="timezone", type="string", length=50, nullable=false)
     */
    private $timezone;


    /**
     * @ORM\Column(name="limit_aid", type="integer")
     * @Assert\NotBlank()
     */
    private $limitAid;

    /**
     * @ORM\Column(name="limit_loc", type="integer")
     * @Assert\NotBlank()
     */
    private $limitLoc;

    /**
     * @ORM\Column(name="limit_iodev", type="integer")
     * @Assert\NotBlank()
     */
    private $limitIoDev;

    /**
     * @ORM\Column(name="limit_client", type="integer")
     * @Assert\NotBlank()
     */
    private $limitClientApp;


    /**
     * @ORM\Column(name="limit_schedule", type="integer", options={"default"=10})
     */
    private $limitSchedule;

    /**
     * @ORM\OneToMany(targetEntity="AccessID", mappedBy="user", cascade={"persist"})
     **/
    private $accessids;

    /**
     * @ORM\OneToMany(targetEntity="Location", mappedBy="user", cascade={"persist"})
     **/
    private $locations;

    /**
     * @ORM\OneToMany(targetEntity="IODevice", mappedBy="user", cascade={"persist"})
     **/
    private $iodevices;

    /**
     * @ORM\OneToMany(targetEntity="Schedule", mappedBy="user", cascade={"persist"})
     **/
    private $schedules;

    public function __construct()
    {
        $this->limitAid = 10;
        $this->limitLoc = 10;
        $this->limitIoDev = 100;
        $this->limitClientApp = 200;
        $this->limitSchedule = 10;
        $this->accessids = new ArrayCollection();
        $this->locations = new ArrayCollection();
        $this->iodevices = new ArrayCollection();
        $this->schedules = new ArrayCollection();
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->regDate = new \DateTime();
        $this->passwordRequestedAt = null;
        $this->lastLogin = null;
        $this->enabled = false;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function setUsername($username)
    {
        $this->email = $username;

        return $this;
    }

    public function getRecaptcha()
    {
        return $this->recaptcha;
    }

    public function setRecaptcha($recaptcha)
    {
        $this->recaptcha = $recaptcha;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword()
    {

        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;
        return $this;
    }

    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    /**
     * Gets the registration time.
     *
     * @return \DateTime
     */
    public function getRegDate()
    {
        return $this->regDate;
    }

    /**
     * Gets the last login time.
     *
     * @return \DateTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    public function setLastLogin(\DateTime $time)
    {
        $this->lastLogin = $time;

        return $this;
    }

    public function getLastIpv4()
    {
        return long2ip($this->lastIpv4);
    }

    public function setLastIpv4($ipstring)
    {
        $this->lastIpv4 = ip2long($ipstring);
    }

    /**
     * Gets the current login time.
     *
     * @return \DateTime
     */
    public function getCurrentLogin()
    {
        return $this->currentLogin;
    }

    public function setCurrentLogin(\DateTime $time)
    {
        $this->currentLogin = $time;

        return $this;
    }

    public function getCurrentIpv4()
    {
        return long2ip($this->currentIpv4);
    }

    public function setCurrentIpv4($ipstring)
    {
        $this->currentIpv4 = ip2long($ipstring);
    }

    public function moveCurrentLoginToLastLogin()
    {
        $this->lastLogin = $this->currentLogin;
        $this->lastIpv4 = $this->currentIpv4;

    }

    public function getToken()
    {
        return $this->token;
    }

    public function genToken()
    {
        $bytes = false;

        if (function_exists('openssl_random_pseudo_bytes')) {
            $crypto_strong = true;
            $bytes = openssl_random_pseudo_bytes(32, $crypto_strong);
        }

        if ($bytes === false) {

            $logger = $this->get('logger');

            if ($logger !== null) {
                $logger->info('OpenSSL did not produce a secure random number.');
            }

            $bytes = hash('sha256', uniqid(mt_rand(), true), true);
        }

        $this->setToken(rtrim(strtr(base64_encode($bytes), '+/', '-_'), '='));
    }

    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    public function setPasswordRequestedAt(\DateTime $date = null)
    {
        $this->passwordRequestedAt = $date;

        return $this;
    }

    /**
     * Gets the timestamp that the user requested a password reset.
     *
     * @return null|\DateTime
     */
    public function getPasswordRequestedAt()
    {
        return $this->passwordRequestedAt;
    }

    /**
     * Returns the user roles
     *
     * @return array The roles
     */
    public function getRoles()
    {
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles)
    {
        $this->roles = array();

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled($boolean)
    {
        $this->enabled = (Boolean)$boolean;

        return $this;
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function getAccessIDS()
    {
        return $this->accessids;
    }

    public function getLocations()
    {
        return $this->locations;
    }

    public function getIODevices()
    {
        return $this->iodevices;
    }

    public function getLimitAid()
    {
        return $this->limitAid;
    }

    public function getLimitLoc()
    {
        return $this->limitLoc;
    }

    public function getTimezone()
    {
        return $this->timezone;
    }

    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    public function getLimitSchedule()
    {
        return $this->limitSchedule;
    }

    public function setLimitSchedule($limitSchedule)
    {
        $this->limitSchedule = $limitSchedule;
    }

    /** Schedule[] */
    public function getSchedules()
    {
        return $this->schedules;
    }

    public function setSchedules($schedules)
    {
        $this->schedules = $schedules;
    }

    public function isLimitScheduleExceeded()
    {
        return $this->getLimitSchedule() > 0 && count($this->getSchedules()) >= $this->getLimitSchedule();
    }
}
