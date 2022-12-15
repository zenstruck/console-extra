<?php

/*
 * This file is part of the zenstruck/console-extra package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CommandRunner
{
    private Command $command;
    private Input $input;
    private ?OutputInterface $output = null;

    private function __construct(Command $command, ?Input $input = null)
    {
        $this->command = $command;
        $this->input = $input ?? new ArrayInput([]);
    }

    /**
     * @param string|array<string,mixed>|null $arguments arg --opt
     *                                                   ['arg' => 'value', 'opt' => true]
     */
    public static function for(Command $command, $arguments = null): self
    {
        $input = null;

        if (null !== $arguments) {
            $input = \is_string($arguments) ? new StringInput($arguments) : new ArrayInput($arguments);
        }

        return new self($command, $input);
    }

    /**
     * @param string|class-string|array<string,mixed> $cli my:command arg --opt
     *                                                     MyCommand::class
     *                                                     ['command' => 'my:command', 'arg' => 'value']
     */
    public static function from(Application $application, $cli): self
    {
        if (!$command = \is_string($cli) ? \explode(' ', $cli)[0] : $cli['command'] ?? null) {
            throw new \InvalidArgumentException('Unknown command. When using an array for $cli, the "command" key must be set.');
        }

        foreach ($application->all() as $commandObject) {
            if ($command === \get_class($commandObject)) {
                return new self($commandObject);
            }
        }

        return self::for($application->find($command), $cli);
    }

    /**
     * @immutable
     */
    public function withOutput(OutputInterface $output): self
    {
        $self = clone $this;
        $self->output = $output;

        return $self;
    }

    /**
     * @param string[] $inputs Interactive inputs to use for the command
     */
    public function run(array $inputs = []): int
    {
        return $this->command->run($this->createInput($inputs), $this->output ?? new NullOutput());
    }

    /**
     * @param string[] $inputs
     */
    private function createInput(array $inputs): Input
    {
        $input = clone $this->input;
        $input->setInteractive(false);

        if (!$inputs) {
            return $input;
        }

        if (!$stream = \fopen('php://memory', 'r+', false)) {
            throw new \RuntimeException('Failed to open stream.');
        }

        foreach ($inputs as $value) {
            \fwrite($stream, $value.\PHP_EOL);
        }

        \rewind($stream);

        $input->setStream($stream);
        $input->setInteractive(true);

        return $input;
    }
}
