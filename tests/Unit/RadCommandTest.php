<?php

namespace Zenstruck\RadCommand\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Zenstruck\Console\Test\TestCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\FullConfigurationCommand;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class RadCommandTest extends TestCase
{
    /**
     * @test
     */
    public function default_name_is_parsed_from_class_name(): void
    {
        $this->assertSame('app:full-configuration', FullConfigurationCommand::getDefaultName());
        $this->assertSame('app:full-configuration', (new FullConfigurationCommand())->getName());
    }

    /**
     * @test
     */
    public function default_description_is_parsed_from_docblock(): void
    {
        $this->assertSame('This is the command description.', FullConfigurationCommand::getDefaultDescription());
        $this->assertSame('This is the command description.', (new FullConfigurationCommand())->getDescription());
    }

    /**
     * @test
     */
    public function help_is_parsed_from_docblock(): void
    {
        $this->assertSame(<<<EOF
        This is the command's help.

        You

        can use

        multiple lines.
        EOF, (new FullConfigurationCommand())->getHelp());
    }

    /**
     * @test
     */
    public function arguments_and_options_are_parsed_from_docblock(): void
    {
        $definition = (new FullConfigurationCommand())->getDefinition();

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
        $this->assertFalse($arg->isArray());
        $this->assertSame('Forth argument is optional with a default value (with spaces)', $arg->getDescription());
        $this->assertSame('default with space', $arg->getDefault());

        $arg = $definition->getArgument('arg5');
        $this->assertFalse($arg->isRequired());
        $this->assertTrue($arg->isArray());
        $this->assertSame('Fifth argument is an optional array', $arg->getDescription());
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
        $this->assertFalse($option->isArray());
        $this->assertSame('default with space', $option->getDefault());
        $this->assertSame('Forth option with default value (with spaces)', $option->getDescription());
        $this->assertNull($option->getShortcut());
        $this->assertTrue($option->isValueRequired());

        $option = $definition->getOption('option5');
        $this->assertTrue($option->isArray());
        $this->assertSame([], $option->getDefault());
        $this->assertSame('Fifth option is an array with a shortcut (-o)', $option->getDescription());
        $this->assertSame('o', $option->getShortcut());
        $this->assertTrue($option->isValueRequired());
    }

    /**
     * @test
     */
    public function can_invoke_with_io_arguments(): void
    {
        TestCommand::for(new FullConfigurationCommand())
            ->addArgument('value1')
            ->execute()
            ->assertSuccessful()
            ->assertOutputContains('$io: Zenstruck\RadCommand\IO')
            ->assertOutputContains('$input: Zenstruck\RadCommand\IO')
            ->assertOutputContains('$output: Zenstruck\RadCommand\IO')
            ->assertOutputContains('$style: Zenstruck\RadCommand\IO')
            ->assertOutputContains('arg1: "value1"')
            ->assertOutputContains('arg2: null')
            ->assertOutputContains('arg3: "default"')
            ->assertOutputContains('arg4: "default with space"')
            ->assertOutputContains('arg5: []')
            ->assertOutputContains('option1: false')
            ->assertOutputContains('option2: null')
            ->assertOutputContains('option3: "default"')
            ->assertOutputContains('option4: "default with space"')
            ->assertOutputContains('option5: []')
        ;
    }
}
