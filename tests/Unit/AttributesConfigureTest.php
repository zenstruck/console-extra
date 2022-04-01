<?php

namespace Zenstruck\Console\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Zenstruck\Console\Attribute\Argument;
use Zenstruck\Console\Attribute\Option;
use Zenstruck\Console\ConfigureWithAttributes;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @requires PHP 8
 */
final class AttributesConfigureTest extends TestCase
{
    /**
     * @test
     */
    public function parse_arguments_and_options(): void
    {
        $command = new WithAttributesCommand();
        $definition = $command->getDefinition();

        $arg = $definition->getArgument('arg1');
        $this->assertTrue($arg->isRequired());
        $this->assertFalse($arg->isArray());
        $this->assertSame('First argument is required', $arg->getDescription());
        $this->assertNull($arg->getDefault());

        $arg = $definition->getArgument('arg2');
        $this->assertFalse($arg->isRequired());
        $this->assertFalse($arg->isArray());
        $this->assertSame('Second argument is optional', $arg->getDescription());
        $this->assertNull($arg->getDefault());

        $arg = $definition->getArgument('arg3');
        $this->assertFalse($arg->isRequired());
        $this->assertFalse($arg->isArray());
        $this->assertSame('Third argument is optional with a default value', $arg->getDescription());
        $this->assertSame('default', $arg->getDefault());

        $arg = $definition->getArgument('arg4');
        $this->assertFalse($arg->isRequired());
        $this->assertTrue($arg->isArray());
        $this->assertSame('Fourth argument is an optional array', $arg->getDescription());
        $this->assertSame([], $arg->getDefault());

        $option = $definition->getOption('option1');
        $this->assertFalse($option->isArray());
        $this->assertFalse($option->getDefault());
        $this->assertSame('First option (no value)', $option->getDescription());
        $this->assertNull($option->getShortcut());
        $this->assertFalse($option->isValueRequired());

        $option = $definition->getOption('option2');
        $this->assertFalse($option->isArray());
        $this->assertNull($option->getDefault());
        $this->assertSame('Second option (value required)', $option->getDescription());
        $this->assertNull($option->getShortcut());
        $this->assertTrue($option->isValueRequired());

        $option = $definition->getOption('option3');
        $this->assertFalse($option->isArray());
        $this->assertSame('default', $option->getDefault());
        $this->assertSame('Third option with default value', $option->getDescription());
        $this->assertNull($option->getShortcut());
        $this->assertTrue($option->isValueRequired());

        $option = $definition->getOption('option4');
        $this->assertTrue($option->isArray());
        $this->assertSame([], $option->getDefault());
        $this->assertSame('Fourth option is an array with a shortcut (-o)', $option->getDescription());
        $this->assertSame('o', $option->getShortcut());
        $this->assertTrue($option->isValueRequired());
    }
}

#[Argument('arg1', InputArgument::REQUIRED, 'First argument is required')]
#[Argument('arg2', null, 'Second argument is optional')]
#[Argument('arg3', null, 'Third argument is optional with a default value', 'default')]
#[Argument('arg4', InputArgument::IS_ARRAY, 'Fourth argument is an optional array')]
#[Option('option1', null, null, 'First option (no value)')]
#[Option('option2', null, InputOption::VALUE_REQUIRED, 'Second option (value required)')]
#[Option('option3', null, InputOption::VALUE_REQUIRED, 'Third option with default value', 'default')]
#[Option('option4', 'o', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Fourth option is an array with a shortcut (-o)')]
class WithAttributesCommand extends Command
{
    use ConfigureWithAttributes;
}
