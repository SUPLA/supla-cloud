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

namespace SuplaBundle\Twig;

use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

// http://symfony.com/doc/current/frontend/custom_version_strategy.html
class WebpackAssetVersionStrategy implements VersionStrategyInterface {
    /** @var bool */
    private $webpackDevServer;
    /** @var string[] */
    private $hashes;

    public function __construct(bool $webpackDevServer, array $hashes) {
        $this->webpackDevServer = $webpackDevServer;
        if (count($hashes) === 0 && php_sapi_name() !== 'cli') {
            throw new \RuntimeException('Invalid frontend configuration. '
                . 'Please build the frontend code with the following command: composer run-script webpack');
        }
        $this->hashes = $hashes;
    }

    public function getVersion($path) {
        if ($this->webpackDevServer) {
            return $this->getFilenameWithHash($path);
        }
        return '';
    }

    public function applyVersion($path) {
        if (!$this->webpackDevServer) {
            $path = dirname($path) . '/' . $this->getFilenameWithHash($path);
        }
        return $path;
    }

    private function getFilenameWithHash($path) {
        preg_match('#.*assets/dist/(.+)\.js$#', $path, $match);
        $filename = $match[1] ?? $path;
        return isset($this->hashes[$filename]) ? (basename($this->hashes[$filename]) ?? $path) : $path;
    }
}
