<?php

namespace Zenstruck\RadCommand\Tests\Fixture\Command;

use Zenstruck\RadCommand;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CustomIOCommand extends RadCommand
{
    public function __invoke(CustomIO $io)
    {
        $io->success('Done!');
    }
}
