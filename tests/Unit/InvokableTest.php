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
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zenstruck\Callback\Exception\UnresolveableArgument;
use Zenstruck\Console\Invokable;
use Zenstruck\Console\IO;
use Zenstruck\Console\Test\TestCommand;
use Zenstruck\Console\Test\TestInput;
use Zenstruck\Console\Test\TestOutput;
use Zenstruck\Console\Tests\Fixture\Command\InvokableCommand;
use Zenstruck\Console\Tests\Fixture\CustomIO;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class InvokableTest extends TestCase
{
    /**
     * @test
     */
    public function invoke_auto_injects_proper_objects(): void
    {
        TestCommand::for(
            new class() extends InvokableCommand {
                public function __invoke(
                    IO $io,
                    InputInterface $input,
                    OutputInterface $output,
                    StyleInterface $style,
                    $none,
                    $arg1,
                    string $arg2,
                    $opt1,
                    bool $opt2,
                    ?string $optional = null,
                ) {
                    $io->comment(\sprintf('IO: %s', $io::class));
                    $io->comment(\sprintf('$this->io(): %s', \get_class($this->io())));
                    $io->comment(\sprintf('InputInterface: %s', $input::class));
                    $io->comment(\sprintf('OutputInterface: %s', $output::class));
                    $io->comment(\sprintf('StyleInterface: %s', $style::class));
                    $io->comment(\sprintf('none: %s', $none::class));
                    $io->comment(\sprintf('arg1: %s', \var_export($arg1, true)));
                    $io->comment(\sprintf('arg2: %s', \var_export($arg2, true)));
                    $io->comment(\sprintf('opt1: %s', \var_export($opt1, true)));
                    $io->comment(\sprintf('opt2: %s', \var_export($opt2, true)));

                    $io->success('done!');
                }

                protected function configure(): void
                {
                    parent::configure();

                    $this
                        ->addArgument('arg1')
                        ->addArgument('arg2')
                        ->addOption('opt1')
                        ->addOption('opt2')
                    ;
                }
            })
            ->execute('foo bar --opt2')
            ->assertSuccessful()
            ->assertOutputContains(\sprintf('IO: %s', IO::class))
            ->assertOutputContains(\sprintf('$this->io(): %s', IO::class))
            ->assertOutputContains(\sprintf('InputInterface: %s', TestInput::class))
            ->assertOutputContains(\sprintf('OutputInterface: %s', TestOutput::class))
            ->assertOutputContains(\sprintf('StyleInterface: %s', IO::class))
            ->assertOutputContains(\sprintf('none: %s', IO::class))
            ->assertOutputContains("arg1: 'foo'")
            ->assertOutputContains("arg2: 'bar'")
            ->assertOutputContains('opt1: false')
            ->assertOutputContains('opt2: true')
        ;
    }

    /**
     * @test
     */
    public function invoke_can_return_integer(): void
    {
        TestCommand::for(
            new class() extends InvokableCommand {
                public function __invoke(): int
                {
                    return 1;
                }
            })
            ->execute()
            ->assertStatusCode(1)
        ;
    }

    /**
     * @test
     */
    public function invoke_must_return_void_or_int(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('::__invoke()" must return void|null|int. Got "string".');

        TestCommand::for(
            new class() extends InvokableCommand {
                public function __invoke(): string
                {
                    return 'invalid';
                }
            })
            ->execute()
        ;
    }

    /**
     * @test
     */
    public function invoke_is_required(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('must implement __invoke() to use %s.', Invokable::class));

        TestCommand::for(new class() extends InvokableCommand {})->execute();
    }

    /**
     * @test
     */
    public function cannot_inject_unknown_parameters(): void
    {
        $this->expectException(UnresolveableArgument::class);

        TestCommand::for(
            new class() extends InvokableCommand {
                public function __invoke(Table $table)
                {
                }
            })
            ->execute()
        ;
    }

    /**
     * @test
     */
    public function can_inject_custom_io(): void
    {
        TestCommand::for(
            new class() extends InvokableCommand {
                public function __invoke(CustomIO $io)
                {
                    $io->success('Success!');
                }
            })
            ->execute()
            ->assertSuccessful()
            ->assertOutputNotContains('Success!')
            ->assertOutputContains('OVERRIDE')
        ;
    }

    /**
     * @test
     */
    public function can_set_custom_io_as_argument_factory(): void
    {
        $command = (new class() extends InvokableCommand {
            public function __invoke(IO $io, CustomIO $custom, InputInterface $input, OutputInterface $output, StyleInterface $style, $none, ?string $optional = null)
            {
                $io->comment(\sprintf('IO: %s', $io::class));
                $io->comment(\sprintf('$this->io(): %s', \get_class($this->io())));
                $io->comment(\sprintf('CustomIO: %s', $custom::class));
                $io->comment(\sprintf('InputInterface: %s', $input::class));
                $io->comment(\sprintf('OutputInterface: %s', $output::class));
                $io->comment(\sprintf('StyleInterface: %s', $style::class));
                $io->comment(\sprintf('none: %s', $none::class));
                $io->success('Success!');
            }
        })->addArgumentFactory(IO::class, fn($input, $output) => new CustomIO($input, $output));

        TestCommand::for($command)
            ->execute()
            ->assertSuccessful()
            ->assertOutputContains(\sprintf('IO: %s', CustomIO::class))
            ->assertOutputContains(\sprintf('$this->io(): %s', CustomIO::class))
            ->assertOutputContains(\sprintf('CustomIO: %s', CustomIO::class))
            ->assertOutputContains(\sprintf('InputInterface: %s', TestInput::class))
            ->assertOutputContains(\sprintf('OutputInterface: %s', TestOutput::class))
            ->assertOutputContains(\sprintf('StyleInterface: %s', CustomIO::class))
            ->assertOutputContains(\sprintf('none: %s', CustomIO::class))
            ->assertOutputNotContains('Success!')
            ->assertOutputContains('OVERRIDE')
        ;
    }

    /**
     * @test
     */
    public function can_inject_io_base_classes(): void
    {
        TestCommand::for(
            new class() extends InvokableCommand {
                public function __invoke(OutputStyle $output, SymfonyStyle $style)
                {
                    $output->text(\sprintf('OutputStyle: %s', \get_debug_type($output)));
                    $output->text(\sprintf('SymfonyStyle: %s', \get_debug_type($style)));
                }
            })
            ->execute()
            ->assertSuccessful()
            ->assertOutputContains(\sprintf('OutputStyle: %s', IO::class))
            ->assertOutputContains(\sprintf('SymfonyStyle: %s', IO::class))
        ;
    }

    /**
     * @test
     */
    public function cannot_inject_unsupported_output(): void
    {
        $this->expectException(UnresolveableArgument::class);

        TestCommand::for(
            new class() extends InvokableCommand {
                public function __invoke(StreamOutput $output)
                {
                }
            })
            ->execute()
        ;
    }

    /**
     * @test
     */
    public function cannot_call_io_before_invoking_command(): void
    {
        $command = new class() extends InvokableCommand {
            public function something(): void
            {
                $this->io();
            }
        };

        $this->expectException(\LogicException::class);

        $command->something();
    }
}
