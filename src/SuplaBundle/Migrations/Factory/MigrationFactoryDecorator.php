<?php

namespace SuplaBundle\Migrations\Factory;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Version\MigrationFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MigrationFactoryDecorator implements MigrationFactory {
    private $migrationFactory;
    private $container;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        MigrationFactory $migrationFactory,
        ContainerInterface $container,
        LoggerInterface $logger
    ) {
        $this->migrationFactory = $migrationFactory;
        $this->container = $container;
        $this->logger = $logger;
    }

    public function createVersion(string $migrationClassName): AbstractMigration {
        $instance = $this->migrationFactory->createVersion($migrationClassName);
        if ($instance instanceof ContainerAwareInterface) {
            $instance->setContainer($this->container);
        }
        if ($instance instanceof LoggerAwareInterface) {
            $instance->setLogger($this->logger);
        }
        return $instance;
    }
}
