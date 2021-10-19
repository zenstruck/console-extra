<?php

namespace Zenstruck\RadCommand\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zenstruck\Callback\Exception\UnresolveableArgument;
use Zenstruck\Console\Test\TestCommand;
use Zenstruck\RadCommand\Invokable;
use Zenstruck\RadCommand\IO;
use Zenstruck\RadCommand\Tests\Fixture\Command\InvokableCommand;
use Zenstruck\RadCommand\Tests\Fixture\CustomIO;

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
                public function __invoke(IO $io, InputInterface $input, OutputInterface $output, StyleInterface $style, $none, ?string $optional = null)
                {
                    $io->comment(\sprintf('IO: %s', \get_class($io)));
                    $io->comment(\sprintf('InputInterface: %s', \get_class($input)));
                    $io->comment(\sprintf('OutputInterface: %s', \get_class($output)));
                    $io->comment(\sprintf('StyleInterface: %s', \get_class($style)));
                    $io->comment(\sprintf('none: %s', \get_class($none)));

                    $io->success('done!');
                }
            })
            ->execute()
            ->assertSuccessful()
            ->assertOutputContains(\sprintf('IO: %s', IO::class))
            ->assertOutputContains(\sprintf('InputInterface: %s', StringInput::class))
            ->assertOutputContains(\sprintf('OutputInterface: %s', StreamOutput::class))
            ->assertOutputContains(\sprintf('StyleInterface: %s', IO::class))
            ->assertOutputContains(\sprintf('none: %s', IO::class))
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
                $io->comment(\sprintf('IO: %s', \get_class($io)));
                $io->comment(\sprintf('CustomIO: %s', \get_class($custom)));
                $io->comment(\sprintf('InputInterface: %s', \get_class($input)));
                $io->comment(\sprintf('OutputInterface: %s', \get_class($output)));
                $io->comment(\sprintf('StyleInterface: %s', \get_class($style)));
                $io->comment(\sprintf('none: %s', \get_class($none)));
                $io->success('Success!');
            }
        })->addArgumentFactory(IO::class, fn($input, $output) => new CustomIO($input, $output));

        TestCommand::for($command)
            ->execute()
            ->assertSuccessful()
            ->assertOutputContains(\sprintf('IO: %s', CustomIO::class))
            ->assertOutputContains(\sprintf('CustomIO: %s', CustomIO::class))
            ->assertOutputContains(\sprintf('InputInterface: %s', StringInput::class))
            ->assertOutputContains(\sprintf('OutputInterface: %s', StreamOutput::class))
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
                    $output->text(\sprintf('OutputStyle: %s', get_debug_type($output)));
                    $output->text(\sprintf('SymfonyStyle: %s', get_debug_type($style)));
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
}
