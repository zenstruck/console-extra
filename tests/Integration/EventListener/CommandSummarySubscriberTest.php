<?php

/*
 * This file is part of the zenstruck/console-extra package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Console\Tests\Integration\EventListener;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Console\EventListener\CommandSummarySubscriber;
use Zenstruck\Console\Test\InteractsWithConsole;
use Zenstruck\Console\Tests\Fixture\EventListener\CustomCommandSummarySubscriber;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CommandSummarySubscriberTest extends KernelTestCase
{
    use InteractsWithConsole;

    /**
     * @test
     */
    public function can_add_summary_subscriber(): void
    {
        self::getContainer()->get('event_dispatcher')->addSubscriber(new CommandSummarySubscriber());

        $this->executeConsoleCommand('service-command foo bar')
            ->assertSuccessful()
            ->assertOutputContains('IO: ')
            ->assertOutputContains('Duration: ')
            ->assertOutputContains('Peak Memory: ')
        ;
    }

    /**
     * @test
     */
    public function can_disable_summary_with_custom_subscriber(): void
    {
        self::getContainer()->get('event_dispatcher')->addSubscriber(new CustomCommandSummarySubscriber());

        $this->executeConsoleCommand('service-command foo bar')
            ->assertSuccessful()
            ->assertOutputContains('IO: ')
            ->assertOutputNotContains('Duration: ')
            ->assertOutputNotContains('Peak Memory: ')
        ;
    }
}
