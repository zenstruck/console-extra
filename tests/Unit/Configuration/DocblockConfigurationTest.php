<?php

namespace Zenstruck\RadCommand\Tests\Unit\Configuration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Zenstruck\RadCommand\Tests\Fixture\Command\CommandTagCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\CommandTagWithArgsCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\FullConfigurationCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\HiddenCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\MalformedArgumentCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\MalformedCommandTagArgumentCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\MalformedCommandTagCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\MalformedCommandTagOptionCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\MalformedOptionCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\MultipleCommandTagCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\TraditionalConfigurationCommand;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DocblockConfigurationTest extends TestCase
{
    /**
     * @test
     */
    public function description_is_parsed_from_docblock(): void
    {
        if (\method_exists(Command::class, 'getDefaultDescription')) {
            // Symfony <5.3 does not have this feature
            $this->assertSame('This is the command description.', FullConfigurationCommand::getDefaultDescription());
        }

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

        $this->assertSame('', (new CommandTagCommand())->getHelp());
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
        $this->assertSame('Forth argument is "optional" with a default value (with spaces)', $arg->getDescription());
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
        $this->assertSame('Forth option with "default" value (with spaces)', $option->getDescription());
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
    public function command_can_use_traditional_configuration(): void
    {
        if (\method_exists(Command::class, 'getDefaultDescription')) {
            // Symfony <5.3 does not have this feature
            $this->assertSame('Traditional description', TraditionalConfigurationCommand::getDefaultDescription());
        }

        $command = new TraditionalConfigurationCommand();

        $this->assertSame('Traditional description', $command->getDescription());
        $this->assertSame('Traditional help', $command->getHelp());
        $this->assertSame(['t1'], \array_keys($command->getDefinition()->getArguments()));
        $this->assertSame(['t2'], \array_keys($command->getDefinition()->getOptions()));
    }

    /**
     * @test
     */
    public function can_use_command_docblock_tag_to_set_name(): void
    {
        $this->assertSame('custom:name', CommandTagCommand::getDefaultName());
        $this->assertSame('custom:name', (new CommandTagCommand())->getName());
    }

    /**
     * @test
     */
    public function malformed_argument(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('Argument tag "@argument foo==bar" on "%s" is malformed.', MalformedArgumentCommand::class));

        new MalformedArgumentCommand();
    }

    /**
     * @test
     */
    public function malformed_option(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('Option tag "@option foo==bar" on "%s" is malformed.', MalformedOptionCommand::class));

        new MalformedOptionCommand();
    }

    /**
     * @test
     */
    public function only_one_command_tag_is_allowed(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('"@command" tag can only be used once in "%s".', MultipleCommandTagCommand::class));

        new MultipleCommandTagCommand();
    }

    /**
     * @test
     */
    public function can_add_arguments_and_options_to_command_tag(): void
    {
        $command = new CommandTagWithArgsCommand();

        $this->assertSame('some:command', $command->getName());

        $definition = $command->getDefinition();

        $arg = $definition->getArgument('arg1');
        $this->assertTrue($arg->isRequired());
        $this->assertFalse($arg->isArray());
        $this->assertSame('', $arg->getDescription());
        $this->assertNull($arg->getDefault());

        $arg = $definition->getArgument('arg2');
        $this->assertFalse($arg->isRequired());
        $this->assertFalse($arg->isArray());
        $this->assertSame('', $arg->getDescription());
        $this->assertNull($arg->getDefault());

        $arg = $definition->getArgument('arg3');
        $this->assertFalse($arg->isRequired());
        $this->assertFalse($arg->isArray());
        $this->assertSame('', $arg->getDescription());
        $this->assertSame('default', $arg->getDefault());

        $arg = $definition->getArgument('arg4');
        $this->assertFalse($arg->isRequired());
        $this->assertFalse($arg->isArray());
        $this->assertSame('', $arg->getDescription());
        $this->assertSame('default with space', $arg->getDefault());

        $arg = $definition->getArgument('arg5');
        $this->assertFalse($arg->isRequired());
        $this->assertTrue($arg->isArray());
        $this->assertSame('', $arg->getDescription());
        $this->assertSame([], $arg->getDefault());

        $option = $definition->getOption('option1');
        $this->assertFalse($option->isArray());
        $this->assertFalse($option->getDefault());
        $this->assertSame('', $option->getDescription());
        $this->assertNull($option->getShortcut());
        $this->assertFalse($option->isValueRequired());

        $option = $definition->getOption('option2');
        $this->assertFalse($option->isArray());
        $this->assertNull($option->getDefault());
        $this->assertSame('', $option->getDescription());
        $this->assertNull($option->getShortcut());
        $this->assertTrue($option->isValueRequired());

        $option = $definition->getOption('option3');
        $this->assertFalse($option->isArray());
        $this->assertSame('default', $option->getDefault());
        $this->assertSame('', $option->getDescription());
        $this->assertNull($option->getShortcut());
        $this->assertTrue($option->isValueRequired());

        $option = $definition->getOption('option4');
        $this->assertFalse($option->isArray());
        $this->assertSame('default with space', $option->getDefault());
        $this->assertSame('', $option->getDescription());
        $this->assertNull($option->getShortcut());
        $this->assertTrue($option->isValueRequired());

        $option = $definition->getOption('option5');
        $this->assertTrue($option->isArray());
        $this->assertSame([], $option->getDefault());
        $this->assertSame('', $option->getDescription());
        $this->assertSame('o', $option->getShortcut());
        $this->assertTrue($option->isValueRequired());
    }

    /**
     * @test
     */
    public function command_tag_no_body(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('"@command" tag must have a value in "%s".', MalformedCommandTagCommand::class));

        new MalformedCommandTagCommand();
    }

    /**
     * @test
     */
    public function malformed_command_tag_argument(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('"@command" tag has a malformed argument ("foo==bar") in "%s".', MalformedCommandTagArgumentCommand::class));

        new MalformedCommandTagArgumentCommand();
    }

    /**
     * @test
     */
    public function malformed_command_tag_option(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('"@command" tag has a malformed option ("--foo==bar") in "%s".', MalformedCommandTagOptionCommand::class));

        new MalformedCommandTagOptionCommand();
    }

    /**
     * @test
     */
    public function can_mark_as_hidden_with_hidden_tag(): void
    {
        $this->assertFalse((new FullConfigurationCommand())->isHidden());

        $command = new HiddenCommand();

        $this->assertTrue($command->isHidden());
        $this->assertSame('hidden:command', $command->getName());
    }

    /**
     * @test
     */
    public function hidden_command_is_lazy(): void
    {
        if (\method_exists(Command::class, 'getDefaultDescription')) {
            // Symfony 5.3+ supports setting hidden this way
            $this->assertSame('|hidden:command', HiddenCommand::getDefaultName());
        } else {
            $this->assertSame('hidden:command', HiddenCommand::getDefaultName());
        }
    }
}
