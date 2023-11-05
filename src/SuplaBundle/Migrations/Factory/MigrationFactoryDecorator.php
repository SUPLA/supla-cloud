<?php

namespace SuplaBundle\Migrations\Factory;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Version\MigrationFactory;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use SuplaBundle\Model\Dependencies\ChannelDependencies;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MigrationFactoryDecorator implements MigrationFactory {
    private $migrationFactory;
    private $container;
    /** @var EntityManagerInterface */
    private $em;
    /** @var ChannelDependencies */
    private $channelDependencies;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        MigrationFactory $migrationFactory,
        ContainerInterface $container,
        EntityManagerInterface $em,
        ChannelDependencies $channelDependencies,
        LoggerInterface $logger
    ) {
        $this->migrationFactory = $migrationFactory;
        $this->container = $container;
        $this->em = $em;
        $this->channelDependencies = $channelDependencies;
        $this->logger = $logger;
    }

    public function createVersion(string $migrationClassName): AbstractMigration {
        $instance = $this->migrationFactory->createVersion($migrationClassName);
        if ($instance instanceof ContainerAwareInterface) {
            $instance->setContainer($this->container);
        }
        if ($instance instanceof EntityManagerAware) {
            $instance->setEntityManager($this->em);
        }
        if ($instance instanceof ChannelDependenciesAware) {
            $instance->setChannelDependencies($this->channelDependencies);
        }
        if ($instance instanceof LoggerAwareInterface) {
            $instance->setLogger($this->logger);
        }
        return $instance;
    }
}
