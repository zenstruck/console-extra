<?php

namespace Zenstruck\RadCommand\Tests\Fixture\Command;

use Symfony\Component\Console\Command\Command;
use Zenstruck\RadCommand\Invokable;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class InvokableCommand extends Command
{
    use Invokable;

    protected function configure(): void
    {
        $this->setName('invoke');
    }
}
