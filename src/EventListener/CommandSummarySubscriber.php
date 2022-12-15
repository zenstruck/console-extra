<?php

namespace Zenstruck\Console\EventListener;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Adds the duration and peak memory used to the end of the command's output.
 *
 * @example // Duration: 5 secs, Peak Memory: 10.0 MiB
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class CommandSummarySubscriber implements EventSubscriberInterface
{
    private int $start;

    final public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => 'preCommand',
            ConsoleEvents::TERMINATE => 'postCommand',
        ];
    }

    final public function preCommand(ConsoleCommandEvent $event): void
    {
        if (!$this->isSupported($event)) {
            return;
        }

        $this->start = \time();
    }

    final public function postCommand(ConsoleTerminateEvent $event): void
    {
        if (!isset($this->start)) {
            return;
        }

        $this->summarize($event->getInput(), $event->getOutput(), \time() - $this->start);
    }

    /**
     * Override to customize when a summary should be displayed.
     */
    protected function isSupported(ConsoleCommandEvent $event): bool
    {
        return true;
    }

    /**
     * Override to customize the summary output.
     *
     * @param int $duration Command duration in seconds
     */
    protected function summarize(InputInterface $input, OutputInterface $output, int $duration): void
    {
        $output->writeln(\sprintf(" // Duration: <info>%s</info>, Peak Memory: <info>%s</info>\n",
            Helper::formatTime($duration),
            Helper::formatMemory(\memory_get_peak_usage(true))
        ));
    }
}
