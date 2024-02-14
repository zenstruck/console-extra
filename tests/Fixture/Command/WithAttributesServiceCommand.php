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

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Zenstruck\Console\Attribute\Argument;
use Zenstruck\Console\ConfigureWithAttributes;
use Zenstruck\Console\InvokableServiceCommand;
use Zenstruck\Console\IO;
use Zenstruck\Console\Tests\Fixture\Service\AnInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[AsCommand('with-attributes-service-command')]
final class WithAttributesServiceCommand extends InvokableServiceCommand
{
    use ConfigureWithAttributes;

    public function __invoke(
        IO $io,

        #[Autowire('@imp1')]
        AnInterface $imp1,

        #[Target('imp2')]
        AnInterface $imp,

        #[Autowire('%kernel.environment%')]
        string $environment,

        #[Autowire('%kernel.debug%')]
        bool $debug,

        #[Argument]
        string $arg1,

        #[Argument]
        string $arg2,
    ): void {
        $io->comment('Imp1: '.$imp1->get());
        $io->comment('Imp2: '.$imp->get());
        $io->comment('Env: '.$environment);
        $io->comment('Debug: '.\var_export($debug, true));
        $io->comment('Arg1: '.$arg1);
        $io->comment('Arg2: '.$arg2);
    }
}
