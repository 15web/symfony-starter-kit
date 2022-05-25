<?php

declare(strict_types=1);

namespace App\Test\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class TestCommand extends Command
{
    protected static $defaultName = 'app:test';

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Тест команды');

        return Command::SUCCESS;
    }
}
