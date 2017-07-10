<?php
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
        $filename = basename($path, '.js');
        return $this->hashes[$filename] ?? $filename . '.js';
    }
}
