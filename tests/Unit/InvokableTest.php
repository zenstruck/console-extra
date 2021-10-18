<?php

namespace Zenstruck\RadCommand\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Style\StyleInterface;
use Zenstruck\Callback\Exception\UnresolveableArgument;
use Zenstruck\Console\Test\TestCommand;
use Zenstruck\RadCommand\Invokable;
use Zenstruck\RadCommand\IO;
use Zenstruck\RadCommand\Tests\Fixture\Command\InvokableCommand;

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
}
