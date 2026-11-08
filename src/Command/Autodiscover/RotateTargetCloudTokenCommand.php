<?php

namespace App\Command\Autodiscover;

use App\Command\Cyclic\CyclicCommand;
use App\Model\TargetCloudAuthTokenRotator;
use App\Model\TimeProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;

class RotateTargetCloudTokenCommand extends Command implements CyclicCommand {
    public function __construct(private TargetCloudAuthTokenRotator $rotator, private LockFactory $lockFactory) {
        parent::__construct();
    }

    protected function configure(): void {
        $this
            ->setName('supla:ad:rotate-token')
            ->setDescription('Rotates the target Cloud token in SUPLA Autodiscover.');
    }

    public function shouldRunNow(TimeProvider $timeProvider): bool {
        return $this->rotator->isDue();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $lock = $this->lockFactory->createLock('supla-target-cloud-token-rotation');
        if (!$lock->acquire()) {
            return self::SUCCESS;
        }
        try {
            $rotated = $this->rotator->rotate(true);
            if ($rotated && $output->isVerbose()) {
                $output->writeln('Target Cloud token rotated and verified.');
            }
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln('Could not rotate the target Cloud token.');
            return self::FAILURE;
        } finally {
            $lock->release();
        }
    }
}
