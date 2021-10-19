<?php

namespace Zenstruck\RadCommand\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Zenstruck\Console\Test\TestCommand;
use Zenstruck\RadCommand\IO;
use Zenstruck\RadCommand\Tests\Fixture\Command\InvokableCommand;
use Zenstruck\RadCommand\Tests\Fixture\CustomIO;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class IOTest extends TestCase
{
    /**
     * @test
     */
    public function wraps_input_interface(): void
    {
        $io = new IO(new ArrayInput(['foo' => 'bar', '--bar' => 'foobar']), new NullOutput());

        $this->assertSame('bar', $io->getFirstArgument());
        $this->assertTrue($io->hasParameterOption('foo'));
        $this->assertSame('bar', $io->getParameterOption('foo'));
        $this->assertTrue($io->isInteractive());

        $io->setInteractive(false);

        $this->assertFalse($io->isInteractive());

        $io->bind(new InputDefinition([new InputArgument('foo'), new InputOption('bar')]));
        $io->validate();

        $this->assertSame(['foo' => 'bar'], $io->getArguments());
        $this->assertTrue($io->hasArgument('foo'));
        $this->assertSame('bar', $io->getArgument('foo'));

        $this->assertSame(['bar' => 'foobar'], $io->getOptions());
        $this->assertTrue($io->hasOption('bar'));
        $this->assertSame('foobar', $io->getOption('bar'));

        $io->setArgument('foo', 'baz');
        $io->setOption('bar', 'barfoo');

        $this->assertSame('baz', $io->getArgument('foo'));
        $this->assertSame('barfoo', $io->getOption('bar'));
    }

    /**
     * @test
     */
    public function argument_and_option_aliases(): void
    {
        $io = new IO(
            new ArrayInput(
                ['foo' => 'bar', '--bar' => 'foobar'],
                new InputDefinition([new InputArgument('foo'), new InputOption('bar')])
            ),
            new NullOutput()
        );

        $this->assertSame('bar', $io->argument('foo'));
        $this->assertSame('foobar', $io->option('bar'));
    }

    /**
     * @test
     */
    public function can_access_wrapped_input_and_output(): void
    {
        $io = new IO($input = new StringInput(''), $output = new NullOutput());

        $this->assertSame($input, $io->input());
        $this->assertSame($output, $io->output());
    }

    /**
     * @test
     */
    public function can_progress_iterate(): void
    {
        TestCommand::for(
            new class() extends InvokableCommand {
                public function __invoke(IO $io)
                {
                    foreach ($io->progressIterate(\range(1, 10)) as $step) {
                        // noop
                    }

                    $io->writeln('end of progressbar');
                }
            })
            ->execute()
            ->assertOutputContains(<<<EOF
              0/10 [░░░░░░░░░░░░░░░░░░░░░░░░░░░░]   0%
             10/10 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%

            end of progressbar
            EOF)
        ;
    }

    /**
     * @test
     */
    public function create_table(): void
    {
        TestCommand::for(
            new class() extends InvokableCommand {
                public function __invoke(IO $io)
                {
                    $io->createTable()
                        ->setHeaders(['h1'])
                        ->addRow(['v1'])
                        ->render()
                    ;
                }
            })
            ->execute()
            ->assertOutputContains(" ---- \n  h1  \n ---- \n  v1  \n ----")
        ;
    }

    /**
     * @test
     */
    public function create_appendable_table(): void
    {
        TestCommand::for(
            new class() extends InvokableCommand {
                public function __invoke(IO $io)
                {
                    $table = $io->createTable()
                        ->setHeaders(['h1'])
                        ->addRow(['v1'])
                    ;

                    $table->render();
                    $table->appendRow(['v2']);
                }
            })
            ->splitOutputStreams()
            ->execute()
            ->assertOutputContains(" ---- \n  h1  \n ---- ")
            ->assertOutputContains(" ---- \n  v1  \n ---- ")
            ->assertOutputContains("  v2  \n ---- ")
        ;
    }

    /**
     * @test
     */
    public function get_error_style_returns_io(): void
    {
        $this->assertInstanceOf(IO::class, (new IO(new StringInput(''), new NullOutput()))->getErrorStyle());
        $this->assertInstanceOf(CustomIO::class, (new CustomIO(new StringInput(''), new NullOutput()))->getErrorStyle());
    }
}
