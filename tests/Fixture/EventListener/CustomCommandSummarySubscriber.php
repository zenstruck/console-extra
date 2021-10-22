<?php

namespace Zenstruck\RadCommand\Tests\Fixture\EventListener;

use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Zenstruck\RadCommand\EventListener\CommandSummarySubscriber;
use Zenstruck\RadCommand\Tests\Fixture\Command\ServiceCommand;

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
