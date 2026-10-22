<?php

namespace App\Tests\Controller\Api;

use App\Controller\Api\UserIconController;
use App\Repository\UserIconRepository;
use Gumlet\ImageResize;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class UserIconControllerTest extends TestCase {
    /** @dataProvider iconDimensions */
    public function testAdjustingImageSizePreservesPreviousBehavior(int $width, int $height, int $expectedWidth): void {
        $source = imagecreatetruecolor($width, $height);
        $red = imagecolorallocate($source, 255, 0, 0);
        $blue = imagecolorallocate($source, 0, 0, 255);
        imagefilledrectangle($source, 0, 0, $width - 1, $height - 1, $red);
        imagefilledrectangle($source, 0, (int) ($height / 2), $width - 1, $height - 1, $blue);
        ob_start();
        imagepng($source);
        $input = ob_get_clean();
        imagedestroy($source);

        $controller = new UserIconController($this->createMock(UserIconRepository::class));
        $adjustImageSize = new ReflectionMethod(UserIconController::class, 'adjustImageSize');
        $output = $adjustImageSize->invoke($controller, ImageResize::createFromString($input));

        $dimensions = getimagesizefromstring($output);
        $this->assertSame($expectedWidth, $dimensions[0]);
        $this->assertSame(156, $dimensions[1]);
        $this->assertSame(IMAGETYPE_PNG, $dimensions[2]);
        $this->assertSame(0, ord($output[28]), 'PNG output must not be interlaced.');
        $result = imagecreatefromstring($output);
        $this->assertSame(0xFF0000, imagecolorat($result, (int) ($expectedWidth / 2), 0) & 0xFFFFFF);
        $this->assertSame(0x0000FF, imagecolorat($result, (int) ($expectedWidth / 2), 155) & 0xFFFFFF);
        imagedestroy($result);
    }

    public static function iconDimensions(): array {
        return [
            'large square' => [420, 420, 156],
            'small square' => [100, 100, 156],
            'portrait' => [100, 200, 78],
            'matching aspect ratio' => [420, 312, 210],
            'wide image' => [840, 312, 210],
            'small wide image' => [100, 50, 210],
            'already correct dimensions' => [210, 156, 210],
        ];
    }
}
