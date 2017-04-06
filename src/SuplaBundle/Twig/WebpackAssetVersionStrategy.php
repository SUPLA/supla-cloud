<?php
namespace SuplaBundle\Twig;

use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

// http://symfony.com/doc/current/frontend/custom_version_strategy.html
class WebpackAssetVersionStrategy implements VersionStrategyInterface {
    /** @var bool */
    private $webpackDevServer;
    /** @var string */
    private $hashConfigPath;
    /** @var string[] */
    private $hashes;

    public function __construct(bool $webpackDevServer, string $hashConfigPath) {
        $this->webpackDevServer = $webpackDevServer;
        $this->hashConfigPath = $hashConfigPath;
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
        $filename = basename($path);
        return $this->getWebpackHashes()[$filename] ?? $filename;
    }

    private function getWebpackHashes(): array {
        if (!$this->hashes) {
            $this->hashes = json_decode(file_get_contents($this->hashConfigPath), true);
        }
        return $this->hashes;
    }
}
