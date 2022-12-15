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
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Output\BufferedOutput;
use Zenstruck\Console\CommandRunner;
use Zenstruck\Console\Tests\Fixture\Command\DummyCommand;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CommandRunnerTest extends TestCase
{
    /**
     * @test
     */
    public function can_create_for_command(): void
    {
        $this->assertSame(0, CommandRunner::for(new DummyCommand())->run());
        $this->assertSame(0, CommandRunner::for(new DummyCommand(), 'arg --opt')->run());
    }

    /**
     * @test
     */
    public function can_find_from_application(): void
    {
        $application = new Application();
        $application->add(new DummyCommand());

        $this->assertSame(0, CommandRunner::from($application, DummyCommand::class)->run());
        $this->assertSame(0, CommandRunner::from($application, 'dummy')->run());
        $this->assertSame(0, CommandRunner::from($application, 'dummy arg --opt')->run());
        $this->assertSame(0, CommandRunner::from($application, ['command' => 'dummy'])->run());
        $this->assertSame(0, CommandRunner::from($application, ['command' => 'dummy', 'arg' => 'foo'])->run());
    }

    /**
     * @test
     */
    public function invalid_command_array(): void
    {
        $application = new Application();

        $this->expectException(\InvalidArgumentException::class);

        CommandRunner::from($application, ['foo' => 'bar']);
    }

    /**
     * @test
     */
    public function not_found_command_from_application(): void
    {
        $application = new Application();

        $this->expectException(CommandNotFoundException::class);

        CommandRunner::from($application, 'invalid');
    }

    /**
     * @test
     */
    public function can_pass_output(): void
    {
        $output = new BufferedOutput();
        $command = new DummyCommand();

        CommandRunner::for($command)
            ->withOutput($output)
            ->run()
        ;

        $this->assertSame("arg: NULL\nopt: false\n", $output->fetch());

        CommandRunner::for($command, 'foo --opt')
            ->withOutput($output)
            ->run()
        ;

        $this->assertSame("arg: 'foo'\nopt: true\n", $output->fetch());
    }

    /**
     * @test
     */
    public function can_add_inputs(): void
    {
        $output = new BufferedOutput();
        $command = new DummyCommand();

        CommandRunner::for($command)
            ->withOutput($output)
            ->run(['foo'])
        ;

        $this->assertStringContainsString('Interactive value: foo', $output->fetch());
    }
}
