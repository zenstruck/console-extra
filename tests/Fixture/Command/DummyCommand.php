<?php

/*
 * This file is part of the zenstruck/console-extra package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Console\Tests\Fixture\Command;

use Zenstruck\Console\IO;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DummyCommand extends InvokableCommand
{
    public function __invoke(IO $io): void
    {
        $io->writeln('arg: '.\var_export($io->argument('arg'), true));
        $io->writeln('opt: '.\var_export($io->option('opt'), true));

        if ($io->isInteractive()) {
            $io->writeln('Interactive value: '.$this->io()->ask('value?'));
        }
    }

    protected function configure(): void
    {
        $this
            ->setName('dummy')
            ->addArgument('arg')
            ->addOption('opt')
        ;
    }
}
