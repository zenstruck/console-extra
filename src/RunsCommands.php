<?php

namespace Zenstruck\Console;

use Symfony\Component\Console\Command\Command;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait RunsCommands
{
    /**
     * @param string|class-string|array<string,mixed> $cli my:command arg --opt
     *                                                     MyCommand::class
     *                                                     ['command' => 'my:command', 'arg' => 'value']
     */
    protected function runCommand($cli, array $inputs = []): int
    {
        if (!$this instanceof Command || !\method_exists($this, 'io')) {
            throw new \LogicException(\sprintf('"%s" can only be used with "%s" commands.', __TRAIT__, Invokable::class));
        }

        if (!$application = $this->getApplication()) {
            throw new \LogicException('Application not available.');
        }

        return CommandRunner::from($application, $cli)->withOutput($this->io())->run($inputs);
    }
}
