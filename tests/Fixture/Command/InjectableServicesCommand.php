<?php

namespace Zenstruck\RadCommand\Tests\Fixture\Command;

use Psr\Log\LoggerInterface;
use Zenstruck\RadCommand;
use Zenstruck\RadCommand\IO;
use Zenstruck\RadCommand\Tests\Fixture\OptionalService;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class InjectableServicesCommand extends RadCommand
{
    public function __invoke(IO $io, LoggerInterface $logger, ?OptionalService $optional = null)
    {
        $io->writeln(\sprintf('$logger: %s', $logger));
    }
}
