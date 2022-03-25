<?php

namespace Zenstruck\Console\Tests\Fixture\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\Table;
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
    protected static $defaultName = 'service-command';

    public function __invoke(IO $io, InputInterface $input, OutputInterface $output, StyleInterface $style, $none, LoggerInterface $logger, ?RouterInterface $router = null, ?Table $optional = null): void
    {
        $io->comment(\sprintf('IO: %s', get_debug_type($io)));
        $io->comment(\sprintf('InputInterface: %s', get_debug_type($input)));
        $io->comment(\sprintf('OutputInterface: %s', get_debug_type($output)));
        $io->comment(\sprintf('StyleInterface: %s', get_debug_type($style)));
        $io->comment(\sprintf('none: %s', get_debug_type($none)));
        $io->comment(\sprintf('LoggerInterface: %s', get_debug_type($logger)));
        $io->comment(\sprintf('RouterInterface: %s', get_debug_type($router)));
        $io->comment(\sprintf('Table: %s', get_debug_type($optional)));
        $io->comment(\sprintf('Parameter environment: %s', $this->parameter('kernel.environment')));

        $io->success('done!');
    }
}
