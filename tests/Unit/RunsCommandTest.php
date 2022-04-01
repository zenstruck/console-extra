<?php

namespace Zenstruck\Console\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Zenstruck\Console\RunsCommands;
use Zenstruck\Console\Test\TestCommand;
use Zenstruck\Console\Tests\Fixture\Command\DummyCommand;
use Zenstruck\Console\Tests\Fixture\Command\RunsCommandCommand;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class RunsCommandTest extends TestCase
{
    /**
     * @test
     */
    public function can_run_commands(): void
    {
        $application = new Application();
        $application->add(new DummyCommand());
        $application->add(new RunsCommandCommand());

        TestCommand::from($application, RunsCommandCommand::class)->execute()
            ->assertSuccessful()
            ->assertOutputContains("arg: NULL\nopt: false")
            ->assertOutputContains("arg: 'foo'\nopt: true")
            ->assertOutputContains('Interactive value: foo')
        ;
    }

    /**
     * @test
     */
    public function application_must_be_available(): void
    {
        $command = new RunsCommandCommand();

        $this->expectException(\LogicException::class);

        $command->run(new StringInput(''), new NullOutput());
    }

    /**
     * @test
     */
    public function must_be_used_on_command(): void
    {
        $command = new class() {
            use RunsCommands;

            public function something(): void
            {
                $this->runCommand('foo');
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
            use RunsCommands;

            public function something(): void
            {
                $this->runCommand('foo');
            }
        };

        $this->expectException(\LogicException::class);

        $command->something();
    }
}
