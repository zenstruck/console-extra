<?php

namespace Zenstruck\Console\Tests\Fixture\Command;

use Symfony\Component\Console\Command\Command;
use Zenstruck\Console\Invokable;

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
