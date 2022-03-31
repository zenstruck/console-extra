<?php

namespace Zenstruck\Console\Tests\Fixture\Command;

use Zenstruck\Console\RunsCommands;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class RunsCommandCommand extends InvokableCommand
{
    use RunsCommands;

    public function __invoke(): void
    {
        $this->runCommand(DummyCommand::class);
        $this->runCommand('dummy foo --opt');
        $this->runCommand('dummy', ['foo']);
    }
}
