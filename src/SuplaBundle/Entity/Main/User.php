<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

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

namespace SuplaBundle\Entity\Main;

use Assert\Assertion;
use DateTime;
use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use SuplaBundle\Entity\HasRelationsCount;
use SuplaBundle\Entity\HasRelationsCountTrait;
use SuplaBundle\Entity\Main\OAuth\ApiClient;
use SuplaBundle\Entity\Main\OAuth\ApiClientAuthorization;
use SuplaBundle\EventListener\ApiRateLimit\ApiRateLimitRule;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\Encoder\EncoderAwareInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="SuplaBundle\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="Email already exists")
 * @ORM\Table(name="supla_user", indexes={
 *     @ORM\Index(name="client_reg_enabled_idx", columns={"client_reg_enabled"}),
 *     @ORM\Index(name="iodevice_reg_enabled_idx", columns={"iodevice_reg_enabled"})
 * })
 */
class User implements UserInterface, EncoderAwareInterface, HasRelationsCount {
    use HasRelationsCountTrait;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"basic"})
     */
    private $id;

    /**
     * @ORM\Column(name="short_unique_id", type="string", length=32, unique=true, options={"fixed" = true})
     * @Groups({"basic"})
     */
    private $shortUniqueId;

    /**
     * @ORM\Column(name="long_unique_id", type="string", length=200, unique=true, options={"fixed" = true})
     * @Groups({"longUniqueId"})
     */
    private $longUniqueId;

    /** @ORM\Column(name="salt", type="string", length=32) */
    private $salt;

    /**
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @Groups({"basic"})
     */
    private $email;

    /**
     * @ORM\Column(name="password", type="string", length=64, nullable=true)
     */
    private $password;

    /**
     * @ORM\Column(name="legacy_password", type="string", length=64, nullable=true)
     */
    private $legacyPassword;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     */
    private $plainPassword;

    /**
     * @ORM\Column(name="enabled", type="boolean")
     * @Groups({"basic"})
     */
    private $enabled;

    /**
     * @ORM\Column(name="reg_date", type="utcdatetime")
     */
    private $regDate;

    /**
     * @ORM\Column(name="token", type="string", nullable=true)
     */
    private $token;

    /**
     * @ORM\Column(name="password_requested_at", type="utcdatetime", nullable=true)
     */
    private $passwordRequestedAt;

    /**
     * @ORM\Column(name="account_removal_requested_at", type="utcdatetime", nullable=true)
     */
    private $accountRemovalRequestedAt;

    private $recaptcha;

    /**
     * @ORM\Column(name="timezone", type="string", length=50, nullable=false)
     * @Groups({"basic"})
     */
    private $timezone;

    /**
     * @ORM\Column(name="limit_aid", type="integer", options={"default"=10})
     */
    private $limitAid;

    /**
     * @ORM\Column(name="limit_loc", type="integer", options={"default"=10})
     */
    private $limitLoc;

    /**
     * @ORM\Column(name="limit_iodev", type="integer", options={"default"=100})
     */
    private $limitIoDev;

    /**
     * @ORM\Column(name="limit_client", type="integer", options={"default"=200})
     */
    private $limitClientApp;

    /**
     * @ORM\Column(name="limit_schedule", type="integer", options={"default"=20})
     */
    private $limitSchedule;

    /**
     * @ORM\Column(name="limit_actions_per_schedule", type="integer", options={"default"=20})
     */
    private $limitActionsPerSchedule;

    /**
     * @ORM\Column(name="limit_channel_group", type="integer", options={"default"=20})
     */
    private $limitChannelGroup;

    /**
     * @ORM\Column(name="limit_channel_per_group", type="integer", options={"default"=10})
     */
    private $limitChannelPerGroup;

    /**
     * @ORM\Column(name="limit_direct_link", type="integer", options={"default"=50})
     */
    private $limitDirectLink;

    /**
     * @ORM\Column(name="limit_scene", type="integer", options={"default"=50})
     */
    private $limitScene;

    /**
     * @ORM\Column(name="limit_operations_per_scene", type="integer", options={"default"=20})
     */
    private $limitOperationsPerScene;

    /**
     * @ORM\Column(name="limit_oauth_client", type="integer", options={"default"=20})
     */
    private $limitOAuthClient;

    /**
     * @ORM\Column(name="limit_push_notifications", type="integer", options={"default"=200})
     */
    private $limitPushNotifications;

    /**
     * @ORM\Column(name="limit_push_notifications_per_hour", type="integer", options={"default"=20})
     */
    private $limitPushNotificationsPerHour;

    /**
     * @ORM\Column(name="limit_value_based_triggers", type="integer", options={"default"=50})
     */
    private $limitValueBasedTriggers;

    /**
     * @ORM\OneToMany(targetEntity="AccessID", mappedBy="user", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $accessids;

    /**
     * @ORM\OneToMany(targetEntity="ClientApp", mappedBy="user", cascade={"persist"})
     */
    private $clientApps;

    /**
     * @ORM\OneToMany(targetEntity="IODeviceChannel", mappedBy="user", cascade={"persist"})
     */
    private $channels;

    /**
     * @ORM\OneToMany(targetEntity="UserIcon", mappedBy="user")
     */
    private $userIcons;

    /**
     * @ORM\OneToMany(targetEntity="Location", mappedBy="user", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $locations;

    /**
     * @ORM\OneToMany(targetEntity="IODevice", mappedBy="user", cascade={"persist"})
     */
    private $iodevices;

    /**
     * @ORM\OneToMany(targetEntity="SuplaBundle\Entity\Main\OAuth\ApiClient", mappedBy="user", cascade={"persist"})
     */
    private $apiClients;

    /**
     * @ORM\OneToMany(targetEntity="Schedule", mappedBy="user", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $schedules;

    /**
     * @ORM\OneToMany(targetEntity="IODeviceChannelGroup", mappedBy="user", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $channelGroups;

    /**
     * @ORM\OneToMany(targetEntity="DirectLink", mappedBy="user", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $directLinks;

    /**
     * @ORM\OneToMany(targetEntity="Scene", mappedBy="user", cascade={"persist"})
     */
    private $scenes;

    /**
     * @ORM\OneToMany(targetEntity="ValueBasedTrigger", mappedBy="user")
     */
    private $valueBasedTriggers;

    /**
     * @ORM\OneToMany(targetEntity="PushNotification", mappedBy="user")
     */
    private $pushNotifications;

    /**
     * @ORM\OneToMany(targetEntity="AuditEntry", mappedBy="user", cascade={"persist"})
     */
    private $auditEntries;

    /**
     * @ORM\Column(name="iodevice_reg_enabled", type="utcdatetime", nullable=true)
     * @Groups({"basic"})
     */
    private $ioDevicesRegistrationEnabled;

    /**
     * @ORM\Column(name="client_reg_enabled", type="utcdatetime", nullable=true)
     * @Groups({"basic"})
     */
    private $clientsRegistrationEnabled;

    /**
     * @ORM\Column(name="rules_agreement", type="boolean", options={"default"=false})
     */
    private $rulesAgreement = false;

    /**
     * @ORM\Column(name="cookies_agreement", type="boolean", options={"default"=false})
     */
    private $cookiesAgreement = false;

    /**
     * @ORM\Column(name="locale", type="string", length=5, nullable=true)
     * @Groups({"basic"})
     */
    private $locale = null;

    /**
     * @ORM\Column(name="oauth_compat_username", type="string", length=64, nullable=true,
     *     options={"comment":"For backward compatibility purpose"})
     * @Groups({"basic"})
     */
    private $oauthCompatUserName;

    /**
     * @ORM\Column(name="oauth_compat_password", type="string", length=64, nullable=true,
     *     options={"comment":"For backward compatibility purpose"})
     */
    private $oauthCompatUserPassword;

    private $oauthOldApiCompatEnabled;

    /**
     * @ORM\OneToMany(targetEntity="SuplaBundle\Entity\Main\OAuth\ApiClientAuthorization", mappedBy="user", cascade={"persist"})
     */
    private $apiClientAuthorizations;

    /**
     * @ORM\Column(name="api_rate_limit", type="string", length=100, nullable=true)
     */
    private $apiRateLimit;

    /**
     * @ORM\Column(name="mqtt_broker_enabled", type="boolean", options={"default"=false})
     */
    private $mqttBrokerEnabled = false;

    /**
     * @ORM\Column(name="mqtt_broker_auth_password", type="string", length=128, nullable=true)
     */
    private $mqttBrokerAuthPassword;

    /**
     * @ORM\Column(name="preferences", type="string", length=4096, nullable=false, options={"default"="{}"})
     * @Groups({"basic"})
     */
    private $preferences;

    /**
     * @ORM\Column(name="home_latitude", type="decimal", precision=9, scale=6, nullable=false)
     */
    private $homeLatitude;

    /**
     * @ORM\Column(name="home_longitude", type="decimal", precision=9, scale=6, nullable=false)
     */
    private $homeLongitude;

    const PREDEFINED_LIMITS = [
        'default' => [
            'limitIoDev' => 100,
            'limitClientApp' => 200,
            'limitAid' => 10,
            'limitChannelGroup' => 20,
            'limitChannelPerGroup' => 10,
            'limitDirectLink' => 50,
            'limitLoc' => 10,
            'limitOAuthClient' => 20,
            'limitScene' => 50,
            'limitOperationsPerScene' => 20,
            'limitSchedule' => 20,
            'limitActionsPerSchedule' => 20,
            'limitPushNotifications' => 200,
            'limitPushNotificationsPerHour' => 20,
            'limitValueBasedTriggers' => 50,
        ],
        'big' => [
            'limitIoDev' => 200,
            'limitClientApp' => 300,
            'limitAid' => 50,
            'limitChannelGroup' => 150,
            'limitChannelPerGroup' => 20,
            'limitDirectLink' => 150,
            'limitLoc' => 50,
            'limitOAuthClient' => 50,
            'limitScene' => 150,
            'limitOperationsPerScene' => 50,
            'limitSchedule' => 150,
            'limitActionsPerSchedule' => 40,
            'limitPushNotifications' => 500,
            'limitPushNotificationsPerHour' => 100,
            'limitValueBasedTriggers' => 200,
        ],
    ];

    public function __construct() {
        $this->accessids = new ArrayCollection();
        $this->locations = new ArrayCollection();
        $this->iodevices = new ArrayCollection();
        $this->schedules = new ArrayCollection();
        $this->clientApps = new ArrayCollection();
        $this->scenes = new ArrayCollection();
        $this->pushNotifications = new ArrayCollection();
        $this->valueBasedTriggers = new ArrayCollection();
        $this->apiClientAuthorizations = new ArrayCollection();
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->shortUniqueId = bin2hex(random_bytes(16));
        $this->longUniqueId = bin2hex(random_bytes(100));
        $this->regDate = new DateTime();
        $this->passwordRequestedAt = null;
        $this->enabled = false;
        $this->mqttBrokerEnabled = false;
        $this->mqttBrokerAuthPassword = null;
        $this->preferences = '{}';
        $this->setTimezone(null);
        $this->oauthOldApiCompatEnabled = false;
        foreach (self::PREDEFINED_LIMITS['default'] as $field => $limit) {
            $this->$field = $limit;
        }
    }

    public function getId() {
        return $this->id;
    }

    public function getShortUniqueId(): string {
        return $this->shortUniqueId;
    }

    public function getLongUniqueId(): string {
        return $this->longUniqueId;
    }

    public function getUsername() {
        return $this->oauthOldApiCompatEnabled ? $this->oauthCompatUserName : $this->email;
    }

    public function getRecaptcha() {
        return $this->recaptcha;
    }

    public function setRecaptcha($recaptcha) {
        $this->recaptcha = $recaptcha;
    }

    public function getSalt() {
        return $this->salt;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;

        return $this;
    }

    public function getPassword() {
        if ($this->oauthOldApiCompatEnabled) {
            return $this->oauthCompatUserPassword;
        }
        return null === $this->password ? $this->legacyPassword : $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
        return $this;
    }

    public function getPlainPassword() {
        return $this->plainPassword;
    }

    public function setPlainPassword($password) {
        $this->plainPassword = $password;
        return $this;
    }

    public function eraseCredentials() {
        $this->plainPassword = null;
    }

    /**
     * Gets the registration time.
     *
     * @return DateTime
     */
    public function getRegDate() {
        return $this->regDate;
    }

    public function getToken() {
        return $this->token;
    }

    public function setToken($token) {
        $this->token = $token;
        return $this;
    }

    public function setPasswordRequestedAt(DateTime $date = null) {
        $this->passwordRequestedAt = $date;
        return $this;
    }

    public function getPasswordRequestedAt(): ?DateTime {
        return $this->passwordRequestedAt;
    }

    public function setTokenForAccountRemoval(string $token): string {
        $this->setToken($token);
        $this->accountRemovalRequestedAt = new DateTime();
        return $token;
    }

    public function getAccountRemovalRequestedAt(): ?DateTime {
        return $this->accountRemovalRequestedAt;
    }

    public function getRoles(): array {
        return ['ROLE_USER'];
    }

    public function isEnabled(): bool {
        return $this->enabled;
    }

    public function setEnabled(bool $boolean): void {
        $this->enabled = $boolean;
    }

    /** @return Collection|AccessID[] */
    public function getAccessIDS() {
        return $this->accessids;
    }

    /** @return Collection|ClientApp[] */
    public function getClientApps() {
        return $this->clientApps;
    }

    /** @return Collection|Selectable|IODeviceChannel[] */
    public function getChannels() {
        return $this->channels;
    }

    /** @return Collection|Selectable|UserIcon[] */
    public function getUserIcons() {
        return $this->userIcons;
    }

    /** @return Collection|IODeviceChannelGroup[] */
    public function getChannelGroups() {
        return $this->channelGroups;
    }

    /** @return Collection|DirectLink[] */
    public function getDirectLinks() {
        return $this->directLinks;
    }

    /** @return Collection|Scene[] */
    public function getScenes() {
        return $this->scenes;
    }

    /** @return Collection|Scene[] */
    public function getValueBasedTriggers() {
        return $this->valueBasedTriggers;
    }

    /** @return Collection|Scene[] */
    public function getPushNotifications() {
        return $this->pushNotifications;
    }

    /** @return Collection|Location[] */
    public function getLocations(): Collection {
        return $this->locations;
    }

    /** @return IODevice[]|Collection */
    public function getIODevices() {
        return $this->iodevices;
    }

    /** @return \SuplaBundle\Entity\Main\OAuth\ApiClient[] */
    public function getApiClients() {
        return $this->apiClients;
    }

    public function getLimitAid() {
        return $this->limitAid;
    }

    public function getLimitLoc() {
        return $this->limitLoc;
    }

    public function getLimitChannelGroup(): int {
        return $this->limitChannelGroup;
    }

    public function getLimitChannelPerGroup(): int {
        return $this->limitChannelPerGroup;
    }

    public function getTimezone(): string {
        return $this->timezone;
    }

    public function setTimezone(?string $timezone) {
        try {
            $timezone = $timezone ?: date_default_timezone_get();
            new DateTimeZone($timezone);
            $this->timezone = $timezone;
        } catch (Exception $e) {
            $this->timezone = date_default_timezone_get();
        }
        $timezone = new DateTimeZone($this->timezone);
        $location = $timezone->getLocation();
        if (!$location) {
            $this->setTimezone('Europe/Warsaw');
            return;
        }
        $this->homeLatitude = $location['latitude'];
        $this->homeLongitude = $location['longitude'];
    }

    public function getLimitSchedule(): int {
        return $this->limitSchedule;
    }

    public function getLimitActionsPerSchedule(): int {
        return $this->limitActionsPerSchedule;
    }

    /** Schedule[] */
    public function getSchedules() {
        return $this->schedules;
    }

    public function setSchedules($schedules) {
        $this->schedules = $schedules;
    }

    public function isLimitScheduleExceeded() {
        return $this->getLimitSchedule() > 0 && count($this->getSchedules()) >= $this->getLimitSchedule();
    }

    public function isLimitDirectLinkExceeded() {
        return $this->limitDirectLink > 0 && count($this->getDirectLinks()) >= $this->limitDirectLink;
    }

    public function isLimitSceneExceeded() {
        return $this->limitScene > 0 && count($this->getScenes()) >= $this->limitScene;
    }

    public function isLimitReactionsExceeded(): bool {
        return $this->limitValueBasedTriggers > 0 && count($this->getValueBasedTriggers()) >= $this->limitValueBasedTriggers;
    }

    public function isLimitNotificationsExceeded(): bool {
        if ($this->limitPushNotifications) {
            if ($this->relationsCount) {
                return $this->relationsCount['pushNotifications'] >= $this->limitPushNotifications;
            } else {
                return count($this->getPushNotifications()) >= $this->limitPushNotifications;
            }
        } else {
            return false;
        }
    }

    public function getLimitOperationsPerScene(): int {
        return $this->limitOperationsPerScene;
    }

    public function isLimitOAuthClientExceeded() {
        return $this->limitOAuthClient > 0 && count($this->getApiClients()) >= $this->limitOAuthClient;
    }

    /** @return DateTime|null */
    public function getClientsRegistrationEnabled() {
        if ($this->clientsRegistrationEnabled) {
            $now = new DateTime();
            if ($now->getTimestamp() > $this->clientsRegistrationEnabled->getTimestamp()) {
                $this->clientsRegistrationEnabled = null;
            }
        }
        return $this->clientsRegistrationEnabled;
    }

    public function enableClientsRegistration(int $forHowLongInSeconds) {
        $this->clientsRegistrationEnabled = new DateTime('@' . (time() + $forHowLongInSeconds));
    }

    public function disableClientsRegistration() {
        $this->clientsRegistrationEnabled = null;
    }

    /** @return DateTime|null */
    public function getIoDevicesRegistrationEnabled() {
        if ($this->ioDevicesRegistrationEnabled) {
            $now = new DateTime();
            if ($now->getTimestamp() > $this->ioDevicesRegistrationEnabled->getTimestamp()) {
                $this->ioDevicesRegistrationEnabled = null;
            }
        }
        return $this->ioDevicesRegistrationEnabled;
    }

    public function enableIoDevicesRegistration(int $forHowLongInSeconds) {
        $this->ioDevicesRegistrationEnabled = new DateTime('@' . (time() + $forHowLongInSeconds));
    }

    public function disableIoDevicesRegistration() {
        $this->ioDevicesRegistrationEnabled = null;
    }

    public function getRulesAgreement(): bool {
        return $this->rulesAgreement;
    }

    public function agreeOnRules(): User {
        $this->rulesAgreement = true;
        return $this;
    }

    public function agreeOnCookies(): User {
        $this->cookiesAgreement = true;
        return $this;
    }

    public function getCookiesAgreement(): bool {
        return $this->cookiesAgreement;
    }

    public function hasLegacyPassword(): bool {
        return null !== $this->legacyPassword;
    }

    public function clearLegacyPassword() {
        $this->legacyPassword = null;
    }

    public function getEncoderName() {
        if ($this->hasLegacyPassword()) {
            return 'legacy_encoder';
        }
        return null; // uses the default encoder
    }

    public function getOauthCompatUserName() {
        return $this->oauthCompatUserName;
    }

    public function setOAuthOldApiCompatEnabled() {
        $this->oauthOldApiCompatEnabled = true;
    }

    /** @return ApiRateLimitRule|null */
    public function getApiRateLimit() {
        return $this->apiRateLimit ? new ApiRateLimitRule($this->apiRateLimit) : null;
    }

    /** @param ApiRateLimitRule|null $apiRateLimit */
    public function setApiRateLimit($apiRateLimit) {
        if ($apiRateLimit) {
            if (!$apiRateLimit instanceof ApiRateLimitRule) {
                $apiRateLimit = new ApiRateLimitRule($apiRateLimit);
            }
            Assertion::true($apiRateLimit->isValid(), 'Invalid API rate limit rule. Format: limit/seconds');
            $apiRateLimit = (string)$apiRateLimit;
        }
        $this->apiRateLimit = $apiRateLimit;
    }

    /** @Groups({"basic"}) */
    public function getLimits(): array {
        $limits = [];
        foreach (self::PREDEFINED_LIMITS['default'] as $field => $limit) {
            $publicName = lcfirst(substr($field, strlen('limit')));
            $limits[$publicName] = $this->$field;
        }
        $limits['accessId'] = $limits['aid'];
        unset($limits['aid']);
        $limits['ioDevice'] = $limits['ioDev'];
        unset($limits['ioDev']);
        $limits['location'] = $limits['loc'];
        unset($limits['loc']);
        $limits['oauthClient'] = $limits['oAuthClient'];
        unset($limits['oAuthClient']);
        return $limits;
    }

    /** @Groups({"basic"}) */
    public function getAgreements(): array {
        return [
            'rules' => $this->rulesAgreement,
            'cookies' => $this->cookiesAgreement,
        ];
    }

    public function addApiClientAuthorization(ApiClient $apiClient, string $scope): ApiClientAuthorization {
        $authorization = new ApiClientAuthorization();
        $authorization->setUser($this);
        $authorization->setApiClient($apiClient);
        $authorization->setScope($scope);
        $authorization->setAuthorizationDate(new DateTime());
        $this->apiClientAuthorizations->add($authorization);
        return $authorization;
    }

    /** @return \SuplaBundle\Entity\Main\OAuth\ApiClientAuthorization[] */
    public function getApiClientAuthorizations() {
        return $this->apiClientAuthorizations;
    }

    public function setLocale(string $locale) {
        $this->locale = $locale;
    }

    /** @return string|null */
    public function getLocale() {
        return $this->locale;
    }

    public function isMqttBrokerEnabled(): bool {
        return $this->mqttBrokerEnabled;
    }

    public function setMqttBrokerEnabled(bool $mqttBrokerEnabled) {
        $this->mqttBrokerEnabled = $mqttBrokerEnabled;
    }

    public function hasMqttBrokerAuthPassword(): bool {
        return !!$this->mqttBrokerAuthPassword;
    }

    public function setMqttBrokerAuthPassword(string $mqttBrokerAuthPassword) {
        $this->mqttBrokerAuthPassword = $mqttBrokerAuthPassword;
    }

    public function getPreferences(): array {
        return $this->preferences ? (json_decode($this->preferences, true) ?: []) : [];
    }

    public function setPreference(string $name, $value): void {
        $preferences = $this->getPreferences();
        $preferences[$name] = $value;
        $this->preferences = json_encode($preferences);
    }

    public function getPreference(string $name, $defaultValue = null) {
        return $this->getPreferences()[$name] ?? $defaultValue;
    }

    public function hashValue(string $value): string {
        return sha1($this->longUniqueId . $value);
    }

    public function getHomeLatitude(): float {
        return $this->homeLatitude;
    }

    public function getHomeLongitude(): float {
        return $this->homeLongitude;
    }
}
