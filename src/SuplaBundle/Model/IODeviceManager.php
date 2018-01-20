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
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Enums\RelayFunctionBits;
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

    /** @deprecated */
    public function channelFunctionMap($type = null, $flist = null) {
        $map = [];
        foreach (ChannelType::functions() as $typeId => $functions) {
            $map[$typeId] = array_map(function (ChannelFunction $function) {
                return $function->getValue();
            }, $functions);
            array_unshift($map[$typeId], '0');
        }

        if ($type === null) {
            return $map;
        }

        if ($type instanceof ChannelType) {
            $type = $type->getValue();
        }

        if ($type == SuplaConst::TYPE_RELAY) {
            $fnc = [0];

            if ($flist !== null && is_int($flist)) {
                $supportedFunctions = array_map(function (ChannelFunction $function) {
                    return $function->getValue();
                }, RelayFunctionBits::getSupportedFunctions($flist));
                $fnc = array_merge($fnc, $supportedFunctions);
            }

            return $fnc;
        }

        return $map[$type];
    }

    public function channelTypeToString($type) {
        if (!($type instanceof ChannelType)) {
            $type = new ChannelType(intval($type));
        }
        $result = $type->getCaption();
        return $this->translator->trans($result);
    }

    /** @deprecated */
    public function channelFunctionToString($func) {
        if (!($func instanceof ChannelFunction)) {
            $func = new ChannelFunction(intval($func));
        }
        $result = $func->getCaption();
        return $this->translator->trans($result);
    }

    public function channelFunctionAltIconMax($func) {
        if ($func instanceof ChannelFunction) {
            $func = $func->getValue();
        }
        switch ($func) {
            case ChannelFunction::POWERSWITCH:
                return 4;
            case ChannelFunction::LIGHTSWITCH:
                return 1;
            case ChannelFunction::CONTROLLINGTHEGATE:
                return 2;
            case ChannelFunction::OPENINGSENSOR_GATE:
                return 2;
        }
        return 0;
    }

    public function channelIoToString($type) {
        $result = 'Unknown';
        if (ChannelType::isValid($type)) {
            $result = (new ChannelType($type))->isOutput() ? 'Output' : 'Input';
        }
        return $this->translator->trans($result);
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
                'type' => [SuplaConst::TYPE_RELAY, SuplaConst::TYPE_RELAYHFD4, SuplaConst::TYPE_RELAYG5LA1A, SuplaConst::TYPE_RELAY2XG5LA1A],
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
                'icon_filename' => $channel->getIconFileName(),
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

            if ($function instanceof ChannelFunction) {
                $function = $function->getValue();
            }

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
