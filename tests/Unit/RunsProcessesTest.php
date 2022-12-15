<?php

/*
 * This file is part of the zenstruck/console-extra package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Console\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Zenstruck\Console\Invokable;
use Zenstruck\Console\RunsProcesses;
use Zenstruck\Console\Test\TestCommand;
use Zenstruck\Console\Tests\Fixture\Command\RunsProcessesCommand;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class RunsProcessesTest extends TestCase
{
    /**
     * @test
     */
    public function can_run_process(): void
    {
        TestCommand::for(new RunsProcessesCommand())
            ->execute()
            ->assertSuccessful()
            ->assertOutputContains('Running process: ls')
            ->assertOutputNotContains('composer.json')
        ;
    }

    /**
     * @test
     */
    public function can_run_process_verbose(): void
    {
        TestCommand::for(new RunsProcessesCommand())
            ->execute('-v')
            ->assertSuccessful()
            ->assertOutputContains('Running process: ls')
            ->assertOutputContains('OUT composer.json')
        ;
    }

    /**
     * @test
     */
    public function failed_process(): void
    {
        TestCommand::for(new RunsProcessesCommand())
            ->expectException(\RuntimeException::class, 'Process failed: Command not found.')
            ->execute('--fail')
            ->assertStatusCode(1)
            ->assertOutputContains('Running process: invalid')
        ;
    }

    /**
     * @test
     */
    public function long_command_is_trimmed(): void
    {
        $command = new class('name') extends Command {
            use Invokable, RunsProcesses;

            public function __invoke(): void
            {
                $this->runProcess(\implode(' ', \array_fill(0, 100, 'long string')));
            }
        };

        TestCommand::for($command)
            ->expectException(\RuntimeException::class)
            ->execute()
            ->assertOutputContains('Running process: long string long string long string ...ing long string long string long string')
        ;
    }

    /**
     * @test
     */
    public function must_be_used_on_command(): void
    {
        $command = new class() {
            use RunsProcesses;

            public function something(): void
            {
                $this->runProcess('foo');
            }
        };

        $this->expectException(\LogicException::class);

        $command->something();
    }

    /**
     * @test
     */
    public function must_be_used_on_invokable_command(): void
    {
        $command = new class() extends Command {
            use RunsProcesses;

            public function something(): void
            {
                $this->runProcess('foo');
            }
        };

        $this->expectException(\LogicException::class);

        $command->something();
    }
}
