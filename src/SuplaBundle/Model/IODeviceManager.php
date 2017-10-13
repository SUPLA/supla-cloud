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

namespace SuplaBundle\Model;

// @codingStandardsIgnoreFile

use Doctrine\Common\Persistence\ManagerRegistry;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ScheduleAction;
use SuplaBundle\Supla\SuplaConst;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Translation\TranslatorInterface;
use ZipArchive;

class IODeviceManager {
    protected $translator;
    protected $doctrine;
    protected $dev_rep;
    protected $channel_rep;
    protected $sec;
    protected $template;

    public function __construct(TranslatorInterface $translator, ManagerRegistry $doctrine, TokenStorage $security_token,
                                TwigEngine $template) {
        $this->translator = $translator;
        $this->doctrine = $doctrine;
        $this->dev_rep = $doctrine->getRepository('SuplaBundle:IODevice');
        $this->channel_rep = $doctrine->getRepository('SuplaBundle:IODeviceChannel');
        $this->sec = $security_token;
        $this->template = $template;
    }

    public function channelFunctionMap($type = null, $flist = null) {
        $map[SuplaConst::TYPE_SENSORNO] = ['0', SuplaConst::FNC_OPENINGSENSOR_GATEWAY,
            SuplaConst::FNC_OPENINGSENSOR_GATE,
            SuplaConst::FNC_OPENINGSENSOR_GARAGEDOOR,
            SuplaConst::FNC_OPENINGSENSOR_DOOR,
            SuplaConst::FNC_NOLIQUIDSENSOR,
            SuplaConst::FNC_OPENINGSENSOR_ROLLERSHUTTER,
        	SuplaConst::FNC_OPENINGSENSOR_WINDOW,
        	SuplaConst::FNC_MAILSENSOR,
        ];

        $map[SuplaConst::TYPE_SENSORNC] = $map[SuplaConst::TYPE_SENSORNO];

        $map[SuplaConst::TYPE_RELAYHFD4] = ['0', SuplaConst::FNC_CONTROLLINGTHEGATEWAYLOCK,
            SuplaConst::FNC_CONTROLLINGTHEGATE,
            SuplaConst::FNC_CONTROLLINGTHEGARAGEDOOR,
            SuplaConst::FNC_CONTROLLINGTHEDOORLOCK,
        ];

        $map[SuplaConst::TYPE_RELAYG5LA1A] = ['0', SuplaConst::FNC_CONTROLLINGTHEGATEWAYLOCK,
            SuplaConst::FNC_CONTROLLINGTHEGATE,
            SuplaConst::FNC_CONTROLLINGTHEGARAGEDOOR,
            SuplaConst::FNC_CONTROLLINGTHEDOORLOCK,
            SuplaConst::FNC_POWERSWITCH,
            SuplaConst::FNC_LIGHTSWITCH,
        ];

        $map[SuplaConst::TYPE_2XRELAYG5LA1A] = ['0', SuplaConst::FNC_CONTROLLINGTHEGATEWAYLOCK,
            SuplaConst::FNC_CONTROLLINGTHEGATE,
            SuplaConst::FNC_CONTROLLINGTHEGARAGEDOOR,
            SuplaConst::FNC_CONTROLLINGTHEDOORLOCK,
            SuplaConst::FNC_POWERSWITCH,
            SuplaConst::FNC_LIGHTSWITCH,
            SuplaConst::FNC_CONTROLLINGTHEROLLERSHUTTER,
        ];

        $map[SuplaConst::TYPE_THERMOMETERDS18B20] = ['0', SuplaConst::FNC_THERMOMETER];

        $map[SuplaConst::TYPE_DHT11] = ['0', SuplaConst::FNC_HUMIDITYANDTEMPERATURE];

        $map[SuplaConst::TYPE_DHT21] = $map[SuplaConst::TYPE_DHT11];

        $map[SuplaConst::TYPE_DHT22] = $map[SuplaConst::TYPE_DHT11];

        $map[SuplaConst::TYPE_AM2301] = $map[SuplaConst::TYPE_DHT11];

        $map[SuplaConst::TYPE_AM2302] = $map[SuplaConst::TYPE_DHT11];

        $map[SuplaConst::TYPE_DIMMER] = ['0', SuplaConst::FNC_DIMMER];

        $map[SuplaConst::TYPE_RGBLEDCONTROLLER] = ['0', SuplaConst::FNC_RGBLIGHTING];

        $map[SuplaConst::TYPE_DIMMERANDRGBLED] = ['0', SuplaConst::FNC_DIMMERANDRGBLIGHTING];

        $map[SuplaConst::TYPE_DISTANCESENSOR] = ['0', SuplaConst::FNC_DEPTHSENSOR,
            SuplaConst::FNC_DISTANCESENSOR,
        ];
        
        $map[SuplaConst::TYPE_THERMOMETER] = ['0', SuplaConst::FNC_THERMOMETER];
        $map[SuplaConst::TYPE_HUMIDITYSENSOR] = ['0', SuplaConst::FNC_HUMIDITY];
        $map[SuplaConst::TYPE_HUMIDITYANDTEMPSENSOR] = ['0', SuplaConst::FNC_HUMIDITYANDTEMPERATURE];
        $map[SuplaConst::TYPE_WINDSENSOR] = ['0', SuplaConst::FNC_WINDSENSOR];
        $map[SuplaConst::TYPE_PRESSURESENSOR] = ['0', SuplaConst::FNC_PRESSURESENSOR];
        $map[SuplaConst::TYPE_RAINSENSOR] = ['0', SuplaConst::FNC_RAINSENSOR];
        $map[SuplaConst::TYPE_WEIGHTSENSOR] = ['0', SuplaConst::FNC_WEIGHTSENSOR];
        $map[SuplaConst::TYPE_WEATHER_STATION] = ['0', SuplaConst::FNC_WEATHER_STATION];
        
        if ($type === null) {
            return $map;
        }

        if ($type == SuplaConst::TYPE_RELAY) {
            $fnc = [0];

            if ($flist !== null
                && is_int($flist)
            ) {
                if ($flist & SuplaConst::BIT_RELAYFNC_CONTROLLINGTHEGATEWAYLOCK) {
                    $fnc[] = SuplaConst::FNC_CONTROLLINGTHEGATEWAYLOCK;
                }

                if ($flist & SuplaConst::BIT_RELAYFNC_CONTROLLINGTHEGATE) {
                    $fnc[] = SuplaConst::FNC_CONTROLLINGTHEGATE;
                }

                if ($flist & SuplaConst::BIT_RELAYFNC_CONTROLLINGTHEGARAGEDOOR) {
                    $fnc[] = SuplaConst::FNC_CONTROLLINGTHEGARAGEDOOR;
                }

                if ($flist & SuplaConst::BIT_RELAYFNC_CONTROLLINGTHEDOORLOCK) {
                    $fnc[] = SuplaConst::FNC_CONTROLLINGTHEDOORLOCK;
                }

                if ($flist & SuplaConst::BIT_RELAYFNC_CONTROLLINGTHEROLLERSHUTTER) {
                    $fnc[] = SuplaConst::FNC_CONTROLLINGTHEROLLERSHUTTER;
                }

                if ($flist & SuplaConst::BIT_RELAYFNC_POWERSWITCH) {
                    $fnc[] = SuplaConst::FNC_POWERSWITCH;
                }

                if ($flist & SuplaConst::BIT_RELAYFNC_LIGHTSWITCH) {
                    $fnc[] = SuplaConst::FNC_LIGHTSWITCH;
                }
                
                if ($flist & SuplaConst::BIT_RELAYFNC_STAIRCASETIMER) {
                	$fnc[] = SuplaConst::FNC_STAIRCASETIMER;
                }
            }

            return $fnc;
        }

        return $map[$type];
    }

    public function channelTypeToString($type) {
        $result = 'Unknown';

        switch ($type) {
            case SuplaConst::TYPE_SENSORNO:
                $result = 'Sensor (normal open)';
                break;
            case SuplaConst::TYPE_SENSORNC:
                $result = 'Sensor (normal closed)';
                break;
            case SuplaConst::TYPE_RELAY:
                $result = 'Relay';
                break;
            case SuplaConst::TYPE_RELAYHFD4:
                $result = 'HFD4 Relay';
                break;
            case SuplaConst::TYPE_RELAYG5LA1A:
                $result = 'G5LA1A Relay';
                break;
            case SuplaConst::TYPE_2XRELAYG5LA1A:
                $result = 'G5LA1A Relay x2';
                break;
            case SuplaConst::TYPE_THERMOMETERDS18B20:
                $result = 'DS18B20 Thermometer';
                break;
            case SuplaConst::TYPE_DHT11:
                $result = 'DHT11 Temperature & Humidity Sensor';
                break;
            case SuplaConst::TYPE_DHT21:
                $result = 'DHT21 Temperature & Humidity Sensor';
                break;
            case SuplaConst::TYPE_DHT22:
                $result = 'DHT22 Temperature & Humidity Sensor';
                break;
            case SuplaConst::TYPE_AM2301:
                $result = 'AM2301 Temperature & Humidity Sensor';
                break;
            case SuplaConst::TYPE_AM2302:
                $result = 'AM2302 Temperature & Humidity Sensor';
                break;
            case SuplaConst::TYPE_THERMOMETER:
            	$result = 'Temperature sensor';
            	break;
            case SuplaConst::TYPE_HUMIDITYSENSOR:
            	$result = 'Humidity sensor';
            	break;
            case SuplaConst::TYPE_HUMIDITYANDTEMPSENSOR:
            	$result = 'Temperature & Humidity sensor';
            	break;
            case SuplaConst::TYPE_WINDSENSOR:
            	$result = 'Wind sensor';
            	break;
            case SuplaConst::TYPE_PRESSURESENSOR:
            	$result = 'Pressure sensor';
            	break;
            case SuplaConst::TYPE_RAINSENSOR:
            	$result = 'Rain sensor';
            	break;
            case SuplaConst::TYPE_WEIGHTSENSOR:
            	$result = 'Weight sensor';
            	break;
            case SuplaConst::TYPE_WEATHER_STATION:
            	$result = 'Weather Station';
            	break;
            case SuplaConst::TYPE_DIMMER:
                $result = 'Dimmer';
                break;
            case SuplaConst::TYPE_RGBLEDCONTROLLER:
                $result = 'RGB led controller';
                break;
            case SuplaConst::TYPE_DIMMERANDRGBLED:
                $result = 'Dimmer & RGB led controller';
                break;
            case SuplaConst::TYPE_DISTANCESENSOR:
                $result = 'Distance sensor';
                break;
        }

        return $this->translator->trans($result);
    }

    public function channelFunctionToString($func) {
        $result = 'None';

        switch ($func) {
            case SuplaConst::FNC_CONTROLLINGTHEGATEWAYLOCK:
                $result = 'Gateway lock operation';
                break;
            case SuplaConst::FNC_CONTROLLINGTHEGATE:
                $result = 'Gate operation';
                break;
            case SuplaConst::FNC_CONTROLLINGTHEGARAGEDOOR:
                $result = 'Garage door operation';
                break;
            case SuplaConst::FNC_THERMOMETER:
                $result = 'Thermometer';
                break;
            case SuplaConst::FNC_OPENINGSENSOR_GATEWAY:
                $result = 'Gateway opening sensor';
                break;
            case SuplaConst::FNC_OPENINGSENSOR_GATE:
                $result = 'Gate opening sensor';
                break;
            case SuplaConst::FNC_OPENINGSENSOR_GARAGEDOOR:
                $result = 'Garage door opening sensor';
                break;
            case SuplaConst::FNC_NOLIQUIDSENSOR:
                $result = 'No liquid sensor';
                break;
            case SuplaConst::FNC_CONTROLLINGTHEDOORLOCK:
                $result = 'Door lock operation';
                break;
            case SuplaConst::FNC_OPENINGSENSOR_DOOR:
                $result = 'Door opening sensor';
                break;
            case SuplaConst::FNC_CONTROLLINGTHEROLLERSHUTTER:
                $result = 'Roller shutter operation';
                break;
            case SuplaConst::FNC_OPENINGSENSOR_ROLLERSHUTTER:
                $result = 'Roller shutter opening sensor';
                break;
            case SuplaConst::FNC_POWERSWITCH:
                $result = 'On/Off switch';
                break;
            case SuplaConst::FNC_LIGHTSWITCH:
                $result = 'Light switch';
                break;
            case SuplaConst::FNC_HUMIDITY:
                $result = 'Humidity sensor';
                break;
            case SuplaConst::FNC_HUMIDITYANDTEMPERATURE:
                $result = 'Temperature and humidity sensor';
                break;
            case SuplaConst::FNC_DIMMER:
                $result = 'Dimmer';
                break;
            case SuplaConst::FNC_RGBLIGHTING:
                $result = 'RGB lighting';
                break;
            case SuplaConst::FNC_DIMMERANDRGBLIGHTING:
                $result = 'Dimmer and RGB lighting';
                break;
            case SuplaConst::FNC_DISTANCESENSOR:
                $result = 'Distance sensor';
                break;
            case SuplaConst::FNC_DEPTHSENSOR:
                $result = 'Depth sensor';
                break;
            case SuplaConst::FNC_OPENINGSENSOR_WINDOW:
            	$result = 'Window opening sensor';
            	break;
            case SuplaConst::FNC_MAILSENSOR:
            	$result = 'Mail sensor';
            	break;
            case SuplaConst::FNC_WINDSENSOR:
            	$result = 'Wind sensor';
            	break;
            case SuplaConst::FNC_PRESSURESENSOR:
            	$result = 'Pressure sensor';
            	break;
            case SuplaConst::FNC_RAINSENSOR:
            	$result = 'Rain sensor';
            	break;
            case SuplaConst::FNC_WEIGHTSENSOR:
            	$result = 'Weight sensor';
            	break;
            case SuplaConst::FNC_WEATHER_STATION:
            	$result = 'Weather Station';
            	break;
            case SuplaConst::FNC_STAIRCASETIMER:
            	$result = 'Staircase timer';
            	break;
        }

        return $this->translator->trans($result);
    }

    public function FunctionIsOpeningSensor($func) {

        switch ($func) {
            case SuplaConst::FNC_OPENINGSENSOR_GATEWAY:
            case SuplaConst::FNC_OPENINGSENSOR_GATE:
            case SuplaConst::FNC_OPENINGSENSOR_GARAGEDOOR:
            case SuplaConst::FNC_OPENINGSENSOR_DOOR:
            case SuplaConst::FNC_OPENINGSENSOR_ROLLERSHUTTER:
                return true;
        }

        return false;
    }

    public function channelIoToString($type) {
        $result = 'Unknown';

        switch ($type) {
            case SuplaConst::TYPE_THERMOMETERDS18B20:
            case SuplaConst::TYPE_DHT11:
            case SuplaConst::TYPE_DHT21:
            case SuplaConst::TYPE_DHT22:
            case SuplaConst::TYPE_AM2301:
            case SuplaConst::TYPE_AM2302:
            case SuplaConst::TYPE_SENSORNO:
            case SuplaConst::TYPE_SENSORNC:
            case SuplaConst::TYPE_THERMOMETER:
            case SuplaConst::TYPE_HUMIDITYSENSOR:
            case SuplaConst::TYPE_HUMIDITYANDTEMPSENSOR:
            case SuplaConst::TYPE_WINDSENSOR:
            case SuplaConst::TYPE_PRESSURESENSOR:
            case SuplaConst::TYPE_RAINSENSOR:
            case SuplaConst::TYPE_WEIGHTSENSOR:
            case SuplaConst::TYPE_WEATHER_STATION:
            case SuplaConst::TYPE_DISTANCESENSOR:
                $result = 'Input';
                break;
            case SuplaConst::TYPE_DIMMER:
            case SuplaConst::TYPE_RGBLEDCONTROLLER:
            case SuplaConst::TYPE_DIMMERANDRGBLED:
            case SuplaConst::TYPE_RELAY:
            case SuplaConst::TYPE_RELAYG5LA1A:
            case SuplaConst::TYPE_2XRELAYG5LA1A:
            case SuplaConst::TYPE_RELAYHFD4:
                $result = 'Output';
                break;
        }

        return $this->translator->trans($result);
    }

    public function functionActionMap() {
        return [
            SuplaConst::FNC_CONTROLLINGTHEGATEWAYLOCK => [ScheduleAction::OPEN],
            SuplaConst::FNC_CONTROLLINGTHEDOORLOCK => [ScheduleAction::OPEN],
            SuplaConst::FNC_CONTROLLINGTHEGATE => [ScheduleAction::OPEN, ScheduleAction::CLOSE],
            SuplaConst::FNC_CONTROLLINGTHEGARAGEDOOR => [ScheduleAction::OPEN, ScheduleAction::CLOSE],
            SuplaConst::FNC_CONTROLLINGTHEROLLERSHUTTER => [ScheduleAction::SHUT, ScheduleAction::REVEAL, ScheduleAction::REVEAL_PARTIALLY],
            SuplaConst::FNC_POWERSWITCH => [ScheduleAction::TURN_ON, ScheduleAction::TURN_OFF],
            SuplaConst::FNC_LIGHTSWITCH => [ScheduleAction::TURN_ON, ScheduleAction::TURN_OFF],
            SuplaConst::FNC_DIMMER => [ScheduleAction::SET_RGBW_PARAMETERS],
            SuplaConst::FNC_RGBLIGHTING => [ScheduleAction::SET_RGBW_PARAMETERS],
            SuplaConst::FNC_DIMMERANDRGBLIGHTING => [ScheduleAction::SET_RGBW_PARAMETERS],
        ];
    }

    public function ioDeviceById($id, $user = null) {
        if ($user === null) {
            $user = $this->sec->getToken()->getUser();
        }

        if ($user === null) {
            return null;
        }

        return $this->dev_rep->findOneBy(['user' => $user, 'id' => intval($id)]);
    }

    /**
     * @param int $id
     * @param User $user
     * @return IODeviceChannel|null
     */
    public function channelById($id, $user = null) {
        if ($user === null) {
            $user = $this->sec->getToken()->getUser();
        }

        if ($user === null) {
            return null;
        }

        return $this->channel_rep->findOneBy(['user' => $user, 'id' => intval($id)]);
    }

    public function getChannels(IODevice $iodev) {
        if ($iodev === null || !($iodev instanceof IODevice)) {
            return null;
        }

        return $this->channel_rep->findBy(
            ['iodevice' => $iodev]
        );
    }

    public function getUnattachedOPENINGSENSORs($func, $include = 0) {
        $user = $this->sec->getToken()->getUser();

        return $this->channel_rep->findBy(
            ['user' => $user,
                'type' => [SuplaConst::TYPE_SENSORNO, SuplaConst::TYPE_SENSORNC],
                'function' => $func,
                'param1' => [0, $include],
                'param2' => 0,
                'param3' => 0]
        );
    }

    public function getSensorUnnattachedSubChannels($func, $include = 0) {

        $user = $this->sec->getToken()->getUser();

        return $this->channel_rep->findBy(
            ['user' => $user,
                'type' => [SuplaConst::TYPE_RELAY, SuplaConst::TYPE_RELAYHFD4, SuplaConst::TYPE_RELAYG5LA1A, SuplaConst::TYPE_2XRELAYG5LA1A],
                'function' => $func,
                'param2' => [0, $include],
                'param3' => 0]
        );
    }

    public function channelsToArray($channels) {
        $result = [];

        foreach ($channels as $channel) {
            $item = ['id' => $channel->getId(),
                'number' => $channel->getChannelNumber(),
                'io' => $this->channelIoToString($channel->getType()),
                'type' => $this->channelTypeToString($channel->getType()),
                'function' => $this->channelFunctionToString($channel->getFunction()),
                'function_id' => $channel->getFunction(),
                'function_is_openingsensor' => $this->FunctionIsOpeningSensor($channel->getFunction()),
                'caption' => $channel->getCaption(),
            ];

            $result[$channel->getId()] = $item;
        }

        return $result;
    }

    public function getChannelName(IODeviceChannel $channel) {
        $result = substr($channel->getIoDevice()->getGUIDString(), -12);

        if (strlen($channel->getIoDevice()->getName()) > 0) {
            $result .= ' [' . trim(substr($channel->getIoDevice()->getName(), 0, 50)) . ']';
        }

        $result .= ' ' . $this->channelFunctionToString($channel->getFunction()) . ' #' . $channel->getChannelNumber();

        if (strlen($channel->getCaption()) > 0) {
            $result .= ' [' . substr($channel->getCaption(), 0, 50) . ']';
        }

        return $result;
    }

    private function channelsToTwigParams($_channels) {
        $result = [];

        if (is_array($_channels) === true) {
            foreach ($_channels as $channel) {
                $result[] = ['id' => $channel->getId(), 'name' => $this->getChannelName($channel)];
            }
        }

        return ['channels' => $result];
    }

    public function channelFunctionParamsHtmlTemplate($channel_id, $function = null) {

        $channel = null;
        $cinstance = false;

        if ($channel_id instanceof IODeviceChannel) {
            $channel = $channel_id;
            $function = $channel->getFunction();
            $cinstance = true;
        } else {
            $function = intval($function);
            $channel = $this->channelById($channel_id);
        }

        if ($channel) {
            $tmpl = 'none';
            $subchannel_selected = null;
            $twig_params = ['channel' => $channel];

            switch ($function) {
                case SuplaConst::FNC_CONTROLLINGTHEGATEWAYLOCK:
                case SuplaConst::FNC_CONTROLLINGTHEGATE:
                case SuplaConst::FNC_CONTROLLINGTHEGARAGEDOOR:
                case SuplaConst::FNC_CONTROLLINGTHEDOORLOCK:
                    $timesel[] = ['name' => '0.5', 'val' => '500'];
                    $timesel[] = ['name' => '1', 'val' => '1000'];
                    $timesel[] = ['name' => '2', 'val' => '2000'];

                    if ($function == SuplaConst::FNC_CONTROLLINGTHEGATEWAYLOCK
                        || $function == SuplaConst::FNC_CONTROLLINGTHEDOORLOCK
                    ) {
                        $timesel[] = ['name' => '4', 'val' => '4000'];
                        $timesel[] = ['name' => '6', 'val' => '6000'];
                        $timesel[] = ['name' => '8', 'val' => '8000'];
                        $timesel[] = ['name' => '10', 'val' => '10000'];

                        $twig_params['default_time_val'] = '6000';
                    } else {
                        $twig_params['default_time_val'] = '500';
                    }

                    $os_func = 0;

                    switch ($function) {
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
                    $twig_params['opening_time'] = $channel->getParam1() / 10.00;
                    $twig_params['closing_time'] = $channel->getParam3() / 10.00;
                    $subchannel_selected = $channel->getParam2();

                    break;
                    
                case SuplaConst::FNC_STAIRCASETIMER:
                	$tmpl = 'staircasetimer';
                	$twig_params['relay_time'] = $channel->getParam1() / 10.00;                	
                	break;

                case SuplaConst::FNC_OPENINGSENSOR_GATEWAY:
                case SuplaConst::FNC_OPENINGSENSOR_GATE:
                case SuplaConst::FNC_OPENINGSENSOR_GARAGEDOOR:
                case SuplaConst::FNC_OPENINGSENSOR_DOOR:
                case SuplaConst::FNC_OPENINGSENSOR_ROLLERSHUTTER:
                    $sc_func = 0;

                    switch ($function) {
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

            if (is_int($subchannel_selected) === true
                && $subchannel_selected !== 0
                && $cinstance === true
            ) {
                $subchannel = $this->channelById($subchannel_selected);

                if ($subchannel !== null) {
                    $twig_params['subchannel_selected'] = $this->getChannelName($subchannel);
                }
            }

            return $this->template->render('SuplaBundle:Form:ChannelFunctions/' . $tmpl . '.html.twig', $twig_params);
        }

        return null;
    }

    public function channelGetCSV($channel, $zip_filename = false) {

        if ($channel->getType() != SuplaConst::TYPE_THERMOMETERDS18B20
            && $channel->getType() != SuplaConst::TYPE_DHT11
            && $channel->getType() != SuplaConst::TYPE_DHT21
            && $channel->getType() != SuplaConst::TYPE_DHT22
            && $channel->getType() != SuplaConst::TYPE_AM2301
            && $channel->getType() != SuplaConst::TYPE_AM2302
        	&& $channel->getType() != SuplaConst::TYPE_THERMOMETER
            && $channel->getType() != SuplaConst::TYPE_HUMIDITYSENSOR
            && $channel->getType() != SuplaConst::TYPE_HUMIDITYANDTEMPSENSOR
        ) {
            return false;
        }

        $temp_file = tempnam(sys_get_temp_dir(), 'supla_csv_');

        if ($temp_file !== false) {
            $handle = fopen($temp_file, 'w+');

            if ($channel->getType() == SuplaConst::TYPE_THERMOMETERDS18B20
            		|| $channel->getType() == SuplaConst::TYPE_THERMOMETER) {
                fputcsv($handle, ['Timestamp', 'Date and time', 'Temperature']);

                $sql = "SELECT UNIX_TIMESTAMP(IFNULL(CONVERT_TZ(`date`, @@session.time_zone, ?), `date`)) AS date_ts, ";
                $sql .= "IFNULL(CONVERT_TZ(`date`, @@session.time_zone, ?), `date`) AS date,";
                $sql .= "`temperature` ";
                $sql .= "FROM `supla_temperature_log` WHERE channel_id = ?";

                $stmt = $this->doctrine->getManager()->getConnection()->prepare($sql);
                $stmt->bindValue(1, $this->sec->getToken()->getUser()->getTimezone());
                $stmt->bindValue(2, $this->sec->getToken()->getUser()->getTimezone());
                $stmt->bindValue(3, $channel->getId(), 'integer');
                $stmt->execute();

                while ($row = $stmt->fetch()) {
                    fputcsv($handle, [$row['date_ts'], $row['date'], $row['temperature']]);
                }
            } else {
                fputcsv($handle, ['Timestamp', 'Date and time', 'Temperature', 'Humidity']);

                $sql = "SELECT UNIX_TIMESTAMP(IFNULL(CONVERT_TZ(`date`, @@session.time_zone, ?), `date`)) AS date_ts, ";
                $sql .= "IFNULL(CONVERT_TZ(`date`, @@session.time_zone, ?), `date`) AS date,";
                $sql .= "`temperature`, `humidity` ";
                $sql .= "FROM `supla_temphumidity_log` WHERE channel_id = ?";

                $stmt = $this->doctrine->getManager()->getConnection()->prepare($sql);
                $stmt->bindValue(1, $this->sec->getToken()->getUser()->getTimezone());
                $stmt->bindValue(2, $this->sec->getToken()->getUser()->getTimezone());
                $stmt->bindValue(3, $channel->getId(), 'integer');
                $stmt->execute();

                while ($row = $stmt->fetch()) {
                    fputcsv($handle, [$row['date_ts'], $row['date'], $row['temperature'], $row['humidity']]);
                }
            }

            fclose($handle);

            if ($zip_filename !== false) {
                $zip = new ZipArchive();

                if ($zip->open($temp_file . '.zip', ZipArchive::CREATE) === true) {
                    $zip->addFile($temp_file, $zip_filename . '.csv');
                    $zip->close();
                }

                unlink($temp_file);
                $temp_file = $temp_file . '.zip';
            }

            return $temp_file;
        }

        return false;
    }
}
