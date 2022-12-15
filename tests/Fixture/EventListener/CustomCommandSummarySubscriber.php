<?php

/*
 * This file is part of the zenstruck/console-extra package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Console\Tests\Fixture\EventListener;

use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Zenstruck\Console\EventListener\CommandSummarySubscriber;
use Zenstruck\Console\Tests\Fixture\Command\ServiceCommand;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CustomCommandSummarySubscriber extends CommandSummarySubscriber
{
    protected function isSupported(ConsoleCommandEvent $event): bool
    {
        return !$event->getCommand() instanceof ServiceCommand;
    }
}
