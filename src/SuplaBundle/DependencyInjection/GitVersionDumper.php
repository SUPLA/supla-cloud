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

namespace SuplaBundle\DependencyInjection;

use AppKernel;
use Symfony\Component\Yaml\Yaml;

class GitVersionDumper {
    public static function dumpVersion() {
        $versionFromEnv = getenv('RELEASE_VERSION');
        exec('git describe --tags 2>' . (file_exists('nul') ? 'nul' : '/dev/null'), $output, $result);
        $versionFromDescribe = $output && $result === 0 ? current($output) : null;
        if ($versionFromEnv || $versionFromDescribe) {
            self::dumpBuildConfig($versionFromEnv ?: $versionFromDescribe, $versionFromDescribe);
        } else {
            echo 'Could not detect application version - skipping.';
        }
    }

    private static function dumpBuildConfig(string $version, ?string $versionFull = null): void {
        $config = [
            'supla' => [
                'version' => ltrim($version, 'v'),
                'version_full' => ltrim($versionFull ?: $version, 'v'),
            ],
        ];
        $buildConfig = '# Config generated automatically by Composer - changes will be overwritten' . PHP_EOL . PHP_EOL;
        $buildConfig .= Yaml::dump($config);
        file_put_contents(AppKernel::ROOT_PATH . '/config/config_build.yml', $buildConfig);
    }
}
