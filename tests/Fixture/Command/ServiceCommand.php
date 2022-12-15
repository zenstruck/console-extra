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

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Routing\RouterInterface;
use Zenstruck\Console\InvokableServiceCommand;
use Zenstruck\Console\IO;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ServiceCommand extends InvokableServiceCommand
{
    public function __invoke(
        IO $io,
        InputInterface $input,
        OutputInterface $output,
        StyleInterface $style,
        $none,
        $arg1,
        string $arg2,
        $opt1,
        bool $opt2,
        string $env,
        LoggerInterface $logger,
        ?RouterInterface $router = null,
        ?Table $optional = null
    ): void {
        $io->comment(\sprintf('IO: %s', \get_debug_type($io)));
        $io->comment(\sprintf('InputInterface: %s', \get_debug_type($input)));
        $io->comment(\sprintf('OutputInterface: %s', \get_debug_type($output)));
        $io->comment(\sprintf('StyleInterface: %s', \get_debug_type($style)));
        $io->comment(\sprintf('none: %s', \get_debug_type($none)));
        $io->comment(\sprintf('LoggerInterface: %s', \get_debug_type($logger)));
        $io->comment(\sprintf('RouterInterface: %s', \get_debug_type($router)));
        $io->comment(\sprintf('Table: %s', \get_debug_type($optional)));
        $io->comment(\sprintf('Parameter environment: %s', $this->parameter('kernel.environment')));
        $io->comment(\sprintf('arg1: %s', \var_export($arg1, true)));
        $io->comment(\sprintf('arg2: %s', \var_export($arg2, true)));
        $io->comment(\sprintf('opt1: %s', \var_export($opt1, true)));
        $io->comment(\sprintf('opt2: %s', \var_export($opt2, true)));
        $io->comment(\sprintf('env: %s', \var_export($env, true)));

        $io->success('done!');
    }

    public static function getDefaultName(): string
    {
        return 'service-command';
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::REQUIRED)
            ->addArgument('arg2', InputArgument::REQUIRED)
            ->addOption('opt1')
            ->addOption('opt2')
        ;
    }
}
