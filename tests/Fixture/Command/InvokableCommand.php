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

use Zenstruck\Console\InvokableCommand as BaseInvokableCommand;

/**
 * Makes your command "invokable" to reduce boilerplate.
 *
 *  Auto-injects the following objects into __invoke():
 *
 * @see IO
 * @see InputInterface the "real" input
 * @see OutputInterface the "real" output
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class InvokableCommand extends BaseInvokableCommand
{
    protected function configure(): void
    {
        $this->setName('invoke');
    }
}
