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
        if (count($hashes) === 0) {
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
        return basename($this->hashes[$filename]) ?? $path;
    }
}
