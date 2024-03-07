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

use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Exception\ApiException;
use SuplaBundle\Utils\DatabaseUtils;
use SuplaBundle\Utils\StringUtils;
use ZipArchive;

class MeasurementCsvExporter {
    use CurrentUserAware;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct($measurementLogsEntityManager) {
        $this->entityManager = $measurementLogsEntityManager;
    }

    public function createZipArchiveWithLogs(IODeviceChannel $channel, string $logsType): string {
        $csvDumpPath = $this->dumpLogsToCsv($channel, $logsType ?: 'default');
        $exportedPath = $csvDumpPath;
        $prefix = StringUtils::camelCaseToSnakeCaseLower($logsType ?: 'measurement') . '_';
        $filename = $this->compress($exportedPath, $prefix . $channel->getId() . '.csv');
        return $filename;
    }

    private function dumpLogsToCsv(IODeviceChannel $channel, string $logsType): string {
        $tempFile = tempnam(sys_get_temp_dir(), 'supla_csv_');
        Assertion::string($tempFile, 'Could not generate temporary file.');
        DatabaseUtils::turnOffQueryBuffering($this->entityManager);
        [$csvHeaders, $sqlQuery] = $this->getDataFetchDefinition($channel, $logsType ?: 'default');
        $handle = fopen($tempFile, 'w+');
        fputcsv($handle, $csvHeaders);
        $stmt = $this->entityManager->getConnection()->prepare($sqlQuery);
        $timezone = $this->getCurrentUserOrThrow()->getTimezone();
        $result = $stmt->executeQuery([':timezone' => $timezone, ':channelId' => $channel->getId()]);
        while ($row = $result->fetchNumeric()) {
            fputcsv($handle, $row);
        }
        fclose($handle);
        return $tempFile;
    }

    private function getDataFetchDefinition(IODeviceChannel $channel, string $logsType): array {
        // @codingStandardsIgnoreStart
        $timestampSelect = "UNIX_TIMESTAMP(IFNULL(CONVERT_TZ(`date`, '+00:00', 'SYSTEM'), `date`)) AS date_ts, IFNULL(CONVERT_TZ(`date`, '+00:00', :timezone), `date`) AS date";
        switch ($channel->getFunction()->getId()) {
            case ChannelFunction::THERMOSTAT:
            case ChannelFunction::THERMOSTATHEATPOLHOMEPLUS:
                return [
                    ['Timestamp', 'Date and time', 'On', 'MeasuredTemperature', 'PresetTemperature'],
                    "SELECT $timestampSelect, `on`, `measured_temperature`, `preset_temperature` FROM `supla_thermostat_log` WHERE channel_id = :channelId",
                ];
            case ChannelFunction::IC_ELECTRICITYMETER:
            case ChannelFunction::IC_GASMETER:
            case ChannelFunction::IC_WATERMETER:
            case ChannelFunction::IC_HEATMETER:
                return [
                    ['Timestamp', 'Date and time', 'Counter', 'CalculatedValue'],
                    "SELECT $timestampSelect, `counter`, `calculated_value` / 1000 calculated_value FROM `supla_ic_log` WHERE channel_id = :channelId",
                ];
            case ChannelFunction::ELECTRICITYMETER:
                if ($logsType === 'voltageAberrations') {
                    return [
                        [
                            'Measurement end (timestamp)',
                            'Measurement end',
                            'Measurement time (seconds)',
                            'Phase number',
                            'Total count',
                            'Count above',
                            'Count below',
                            'Seconds above',
                            'Seconds below',
                            'Maximum seconds above',
                            'Maximum seconds below',
                            'Minimum voltage',
                            'Maximum voltage',
                            'Average voltage',
                        ],
                        "SELECT $timestampSelect, measurement_time_sec, phase_no, count_total, count_above, count_below, sec_above, sec_below, max_sec_above, max_sec_below, min_voltage, max_voltage, avg_voltage FROM `supla_em_voltage_aberration_log` WHERE channel_id = :channelId",
                    ];
                } elseif ($logsType === 'voltageHistory') {
                    return [
                        [
                            'Measurement end (timestamp)',
                            'Measurement end',
                            'Phase number',
                            'Minimum voltage',
                            'Maximum voltage',
                            'Average voltage',
                        ],
                        "SELECT $timestampSelect, phase_no, min, max, avg FROM `supla_em_voltage_log` WHERE channel_id = :channelId",
                    ];
                } else {
                    return [
                        [
                            'Timestamp',
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
                            'Phase 3 Reverse reactive Energy kvarh',
                            'Forward active Energy kWh - Vector balance',
                            'Reverse active Energy kWh - Vector balance',
                        ],
                        "SELECT $timestampSelect, IFNULL(`phase1_fae`, 0) / 100000.00 phase1_fae, IFNULL(`phase1_rae`, 0) / 100000.00 phase1_rae, IFNULL(`phase1_fre`, 0) / 100000.00 phase1_fre, IFNULL(`phase1_rre`, 0) / 100000.00 phase1_rre, IFNULL(`phase2_fae`, 0) / 100000.00 phase2_fae, IFNULL(`phase2_rae`, 0) / 100000.00 phase2_rae, IFNULL(`phase2_fre`, 0) / 100000.00 phase2_fre, IFNULL(`phase2_rre`, 0) / 100000.00 phase2_rre, IFNULL(`phase3_fae`, 0) / 100000.00 phase3_fae, IFNULL(`phase3_rae`, 0) / 100000.00 phase3_rae, IFNULL(`phase3_fre`, 0) / 100000.00 phase3_fre, IFNULL(`phase3_rre`, 0) / 100000.00 phase3_rre, IFNULL(`fae_balanced`, 0) / 100000.00 fae_balanced, IFNULL(`rae_balanced`, 0) / 100000.00 rae_balanced FROM `supla_em_log` WHERE channel_id = :channelId",
                    ];
                }
            case ChannelFunction::THERMOMETER:
                return [
                    ['Timestamp', 'Date and time', 'Temperature'],
                    "SELECT $timestampSelect, `temperature` FROM `supla_temperature_log` WHERE channel_id = :channelId",
                ];
            case ChannelFunction::HUMIDITY:
                return [
                    ['Timestamp', 'Date and time', 'Humidity'],
                    "SELECT $timestampSelect, `humidity` FROM `supla_temphumidity_log` WHERE channel_id = :channelId",
                ];
            case ChannelFunction::HUMIDITYANDTEMPERATURE:
                return [
                    ['Timestamp', 'Date and time', 'Temperature', 'Humidity'],
                    "SELECT $timestampSelect, `temperature`, `humidity` FROM `supla_temphumidity_log` WHERE channel_id = :channelId",
                ];
            default:
                throw new ApiException('Cannot generate CSV from this channel - invalid type.');
        }
        // @codingStandardsIgnoreEnd
    }

    private function compress(string $tempFile, string $zipFilename): string {
        $zipPath = $tempFile . '.zip';
        $zip = new ZipArchive();
        Assertion::true($zip->open($zipPath, ZipArchive::CREATE), 'Could not create a ZIP file.');
        $zip->addFile($tempFile, $zipFilename);
        $zip->close();
        unlink($tempFile);
        return $zipPath;
    }
}
