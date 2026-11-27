<?php

namespace App\Tests\Command\Initialization;

use App\Command\Initialization\ConfigurationValidator;
use App\Command\Initialization\InitializeCommand;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ConfigurationValidatorTest extends TestCase {
    #[DataProvider('validUrls')]
    public function testValidUrls(string $url): void {
        (new ConfigurationValidator($url, 'cloud.example.com'))->validate();
        $this->expectNotToPerformAssertions();
    }

    public static function validUrls(): array {
        return [
            ['https://cloud.example.com'],
            ['http://localhost:8080'],
            ['https://127.0.0.1'],
            ['https://[::1]:443'],
        ];
    }

    #[DataProvider('invalidUrls')]
    public function testInvalidUrlsStopInitialization(string $url): void {
        $application = new Application();
        $command = new InitializeCommand([], new ConfigurationValidator($url, 'cloud.example.com'));
        $application->add($command);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid SUPLA_URL');
        (new CommandTester($command))->execute([]);
    }

    public static function invalidUrls(): array {
        return [
            [''],
            ['cloud.example.com'],
            ['https:///path'],
            ['https://cloud.example.com/'],
            ['https://cloud.example.com/path'],
            ['https://cloud.example.com?'],
            ['https://cloud.example.com?foo=bar'],
            ['https://cloud.example.com#'],
            ['https://cloud.example.com#fragment'],
            ['https://user:password@cloud.example.com'],
            ['https://cloud.example.com:'],
            ['https://cloud.example.com:0'],
            ['https://cloud.example.com:65536'],
            ['https://cloud.example.com:abc'],
            ['https://cloud.example.com with spaces'],
        ];
    }

    #[DataProvider('validHosts')]
    public function testValidHosts(string $host): void {
        (new ConfigurationValidator('https://cloud.example.com', $host))->validate();
        $this->expectNotToPerformAssertions();
    }

    public static function validHosts(): array {
        return [
            ['cloud.example.com'],
            ['localhost:8080'],
            ['127.0.0.1'],
            ['[::1]:443'],
        ];
    }

    #[DataProvider('invalidHosts')]
    public function testInvalidHostsStopInitialization(string $host): void {
        $application = new Application();
        $command = new InitializeCommand([], new ConfigurationValidator('https://cloud.example.com', $host));
        $application->add($command);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid SUPLA_HOST_ADDRESS');
        (new CommandTester($command))->execute([]);
    }

    public static function invalidHosts(): array {
        return [
            [''],
            ['https://cloud.example.com'],
            ['cloud.example.com/'],
            ['cloud.example.com/path'],
            ['cloud.example.com?query'],
            ['cloud.example.com#fragment'],
            ['user@cloud.example.com'],
            ['cloud.example.com:'],
            ['cloud.example.com:0'],
            ['cloud.example.com:65536'],
            ['cloud.example.com:abc'],
            ['cloud.example.com with spaces'],
        ];
    }
}
