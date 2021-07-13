<?php

namespace Zenstruck\RadCommand\Tests\Fixture\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Zenstruck\RadCommand;
use Zenstruck\RadCommand\IO;

/**
 * This is the command description.
 *
 * This is the command's help.
 *
 * You
 *
 * can use
 *
 * multiple lines.
 *
 * @argument arg1 First argument is required
 * @argument ?arg2 Second argument is optional
 * @argument arg3=default Third argument is optional with a default value
 * @argument arg4="default with space" Forth argument is optional with a default value (with spaces)
 * @argument ?arg5[] Fifth argument is an optional array
 *
 * @option option1 First option (no value)
 * @option option2= Second option (value required)
 * @option option3=default Third option with default value
 * @option option4="default with space" Forth option with default value (with spaces)
 * @option o|option5[] Fifth option is an array with a shortcut (-o)
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class FullConfigurationCommand extends RadCommand
{
    public function __invoke(IO $io, InputInterface $input, OutputInterface $output, StyleInterface $style)
    {
        $io->writeln(\sprintf('$io: %s', \get_class($io)));
        $io->writeln(\sprintf('$input: %s', \get_class($input)));
        $io->writeln(\sprintf('$output: %s', \get_class($output)));
        $io->writeln(\sprintf('$style: %s', \get_class($style)));

        foreach ($io->getArguments() as $name => $argument) {
            $io->writeln(\sprintf('%s: %s', $name, \json_encode($argument)));
        }

        foreach ($io->getOptions() as $name => $option) {
            $io->writeln(\sprintf('%s: %s', $name, \json_encode($option)));
        }

        $io->success('Done!');
    }
}
