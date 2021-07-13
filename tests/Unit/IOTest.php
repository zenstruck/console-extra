<?php

namespace Zenstruck\RadCommand\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Zenstruck\RadCommand\IO;

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
}
