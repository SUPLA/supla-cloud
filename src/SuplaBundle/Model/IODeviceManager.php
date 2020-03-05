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

use Assert\Assertion;
use Doctrine\Common\Persistence\ManagerRegistry;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;
use ZipArchive;

class IODeviceManager {
    protected $translator;
    protected $doctrine;
    protected $sec;

    public function __construct(
        TranslatorInterface $translator,
        ManagerRegistry $doctrine,
        TokenStorageInterface $security_token
    ) {
        $this->translator = $translator;
        $this->doctrine = $doctrine;
        $this->sec = $security_token;
    }

    /** @deprecated */
    public function channelFunctionToString($func) {
        if (!($func instanceof ChannelFunction)) {
            $func = new ChannelFunction(intval($func));
        }
        $result = $func->getCaption();
        return $this->translator->trans($result);
    }

    public function channelGetCSV(IODeviceChannel $channel, $zip_filename = false) {
        Assertion::inArray($channel->getFunction()->getId(), [
            ChannelFunction::THERMOMETER()->getId(),
            ChannelFunction::HUMIDITY()->getId(),
            ChannelFunction::HUMIDITYANDTEMPERATURE()->getId(),
            ChannelFunction::ELECTRICITYMETER()->getId(),
            ChannelFunction::GASMETER()->getId(),
            ChannelFunction::WATERMETER()->getId(),
            ChannelFunction::HEATMETER()->getId(),
            ChannelFunction::THERMOSTAT()->getId(),
            ChannelFunction::THERMOSTATHEATPOLHOMEPLUS()->getId(),
        ]);

        $temp_file = tempnam(sys_get_temp_dir(), 'supla_csv_');

        if ($temp_file !== false) {
            $handle = fopen($temp_file, 'w+');

            if ($channel->getType()->getId() == ChannelType::THERMOSTAT
                || $channel->getType()->getId() == ChannelType::THERMOSTATHEATPOLHOMEPLUS ) {

                fputcsv($handle, ['Timestamp', 'Date and time', 'On', 'MeasuredTemperature', 'PresetTemperature']);

                $sql = "SELECT UNIX_TIMESTAMP(IFNULL(CONVERT_TZ(`date`, '+00:00', ?), `date`)) AS date_ts, ";
                $sql .= "IFNULL(CONVERT_TZ(`date`, '+00:00', ?), `date`) AS date, `on`, ";
                $sql .= "`measured_temperature`, `preset_temperature` FROM `supla_thermostat_log` WHERE channel_id = ?";

                $stmt = $this->doctrine->getManager()->getConnection()->prepare($sql);
                $stmt->bindValue(1, $this->sec->getToken()->getUser()->getTimezone());
                $stmt->bindValue(2, $this->sec->getToken()->getUser()->getTimezone());
                $stmt->bindValue(3, $channel->getId(), 'integer');
                $stmt->execute();

                while ($row = $stmt->fetch()) {
                    fputcsv($handle, [$row['date_ts'], $row['date'],
                        $row['on'],
                        $row['measured_temperature'],
                        $row['preset_temperature'],
                    ]);
                }

            } elseif ($channel->getType()->getId() == ChannelType::IMPULSECOUNTER) {

                fputcsv($handle, ['Timestamp', 'Date and time', 'Counter', 'CalculatedValue']);

                $sql = "SELECT UNIX_TIMESTAMP(IFNULL(CONVERT_TZ(`date`, '+00:00', ?), `date`)) AS date_ts, ";
                $sql .= "IFNULL(CONVERT_TZ(`date`, '+00:00', ?), `date`) AS date,";
                $sql .= "`counter`, `calculated_value` / 1000 calculated_value FROM `supla_ic_log` WHERE channel_id = ?";

                $stmt = $this->doctrine->getManager()->getConnection()->prepare($sql);
                $stmt->bindValue(1, $this->sec->getToken()->getUser()->getTimezone());
                $stmt->bindValue(2, $this->sec->getToken()->getUser()->getTimezone());
                $stmt->bindValue(3, $channel->getId(), 'integer');
                $stmt->execute();

                while ($row = $stmt->fetch()) {
                    fputcsv($handle, [$row['date_ts'], $row['date'],
                        $row['counter'],
                        $row['calculated_value'],
                    ]);
                }
            } elseif ($channel->getType()->getId() == ChannelType::ELECTRICITYMETER) {

                fputcsv($handle, ['Timestamp',
                    'Date and time',
                    'Phase 1 Forward active Energy kWh',
                    'Phase 1 Reverse active Energy kWh',
                    'Phase 1 Forward reactive Energy kvarh',
                    'Phase 1 Reverse reactive Energy kvarh',
                    'Phase 2 Forward active Energy kWh',
                    'Phase 2 Reverse active Energy kWh',
                    'Phase 2 Forward reactive Energy kvarh',
                    'Phase 2 Reverse reactive Energy kvarh',
                    'Phase 3 Forward active Energy kWh',
                    'Phase 3 Reverse active Energy kWh',
                    'Phase 3 Forward reactive Energy kvarh',
                    'Phase 3 Reverse reactive Energy kvarh']);

                $sql = "SELECT UNIX_TIMESTAMP(IFNULL(CONVERT_TZ(`date`, '+00:00', ?), `date`)) date_ts, ";
                $sql .= "IFNULL(CONVERT_TZ(`date`, '+00:00', ?), `date`) date,";
                $sql .= "IFNULL(`phase1_fae`, 0) / 100000.00 phase1_fae, IFNULL(`phase1_rae`, 0) / 100000.00 phase1_rae, ";
                $sql .= "IFNULL(`phase1_fre`, 0) / 100000.00 phase1_fre, IFNULL(`phase1_rre`, 0) / 100000.00 phase1_rre, ";
                $sql .= "IFNULL(`phase2_fae`, 0) / 100000.00 phase2_fae, IFNULL(`phase2_rae`, 0) / 100000.00 phase2_rae, ";
                $sql .= "IFNULL(`phase2_fre`, 0) / 100000.00 phase2_fre, IFNULL(`phase2_rre`, 0) / 100000.00 phase2_rre, ";
                $sql .= "IFNULL(`phase3_fae`, 0) / 100000.00 phase3_fae, IFNULL(`phase3_rae`, 0) / 100000.00 phase3_rae, ";
                $sql .= "IFNULL(`phase3_fre`, 0) / 100000.00 phase3_fre, IFNULL(`phase3_rre`, 0) / 100000.00 phase3_rre ";
                $sql .= "FROM `supla_em_log` WHERE channel_id = ?";

                $stmt = $this->doctrine->getManager()->getConnection()->prepare($sql);
                $stmt->bindValue(1, $this->sec->getToken()->getUser()->getTimezone());
                $stmt->bindValue(2, $this->sec->getToken()->getUser()->getTimezone());
                $stmt->bindValue(3, $channel->getId(), 'integer');
                $stmt->execute();

                while ($row = $stmt->fetch()) {
                    fputcsv($handle, [$row['date_ts'], $row['date'],
                        $row['phase1_fae'],
                        $row['phase1_rae'],
                        $row['phase1_fre'],
                        $row['phase1_rre'],
                        $row['phase2_fae'],
                        $row['phase2_rae'],
                        $row['phase2_fre'],
                        $row['phase2_rre'],
                        $row['phase3_fae'],
                        $row['phase3_rae'],
                        $row['phase3_fre'],
                        $row['phase3_rre'],
                    ]);
                }
            } elseif ($channel->getType()->getId() == ChannelType::THERMOMETERDS18B20
                || $channel->getType()->getId() == ChannelType::THERMOMETER) {
                fputcsv($handle, ['Timestamp', 'Date and time', 'Temperature']);

                $sql = "SELECT UNIX_TIMESTAMP(IFNULL(CONVERT_TZ(`date`, '+00:00', ?), `date`)) AS date_ts, ";
                $sql .= "IFNULL(CONVERT_TZ(`date`, '+00:00', ?), `date`) AS date,";
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

                $sql = "SELECT UNIX_TIMESTAMP(IFNULL(CONVERT_TZ(`date`, '+00:00', ?), `date`)) AS date_ts, ";
                $sql .= "IFNULL(CONVERT_TZ(`date`, '+00:00', ?), `date`) AS date,";
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
