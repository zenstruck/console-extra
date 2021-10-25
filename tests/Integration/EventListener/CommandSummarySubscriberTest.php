<?php

namespace Zenstruck\Console\Tests\Integration\EventListener;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Console\Test\InteractsWithConsole;
use Zenstruck\Console\EventListener\CommandSummarySubscriber;
use Zenstruck\Console\Tests\Fixture\Command\ServiceCommand;
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
        self::bootKernel();

        self::$kernel->getContainer()->get('event_dispatcher')->addSubscriber(new CommandSummarySubscriber());

        $this->executeConsoleCommand(ServiceCommand::class)
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
        self::bootKernel();

        self::$kernel->getContainer()->get('event_dispatcher')->addSubscriber(new CustomCommandSummarySubscriber());

        $this->executeConsoleCommand(ServiceCommand::class)
            ->assertSuccessful()
            ->assertOutputContains('IO: ')
            ->assertOutputNotContains('Duration: ')
            ->assertOutputNotContains('Peak Memory: ')
        ;
    }
}
