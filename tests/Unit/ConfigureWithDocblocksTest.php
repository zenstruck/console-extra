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
use Zenstruck\Console\Configuration\DocblockConfiguration;
use Zenstruck\Console\Tests\Fixture\Command\AutoNameDocblockCommand;
use Zenstruck\Console\Tests\Fixture\Command\DocblockCommand;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @group legacy
 */
final class ConfigureWithDocblocksTest extends TestCase
{
    /**
     * @test
     */
    public function parse_name_description_and_help(): void
    {
        /**
         * This is the command description.
         *
         * This
         * is
         * the help.
         *
         * @command some:command
         */
        $command = new class() extends DocblockCommand {};

        $this->assertSame('some:command', $command::getDefaultName());
        $this->assertSame('some:command', $command->getName());

        if (DocblockConfiguration::supportsLazy()) {
            // Symfony <5.3 does not have this feature
            $this->assertSame('This is the command description.', $command::getDefaultDescription());
        }

        $this->assertSame('This is the command description.', $command->getDescription());
        $this->assertSame("This\nis\nthe help.", $command->getHelp());

        /**
         * @command some:command
         */
        $command = new class() extends DocblockCommand {};

        if (DocblockConfiguration::supportsLazy()) {
            // Symfony <5.3 does not have this feature
            $this->assertNull($command::getDefaultDescription());
        }

        $this->assertSame('', $command->getDescription());
        $this->assertSame('', $command->getHelp());
    }

    /**
     * @test
     */
    public function parse_arguments_and_options(): void
    {
        /**
         * @command some:command
         *
         * @argument arg1 First argument is required
         * @argument ?arg2 Second argument is optional
         * @argument arg3=default Third argument is optional with a default value
         * @argument arg4="default with space" Forth argument is "optional" with a default value (with spaces)
         * @argument ?arg5[] Fifth argument is an optional array
         *
         * @option option1 First option (no value)
         * @option option2= Second option (value required)
         * @option option3=default Third option with default value
         * @option option4="default with space" Forth option with "default" value (with spaces)
         * @option o|option5[] Fifth option is an array with a shortcut (-o)
         */
        $command = new class() extends DocblockCommand {};
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
     * @group legacy
     */
    public function can_override_docblock_configuration_with_traditional_configuration(): void
    {
        /**
         * Not used description.
         *
         * Not used help.
         *
         * @command not:used:name
         *
         * @argument arg not used
         * @option option not used
         */
        $command = new class() extends DocblockCommand {
            protected static $defaultName = 'traditional:name';
            protected static $defaultDescription = 'Traditional description';

            protected function configure(): void
            {
                $this
                    ->setDescription(self::$defaultDescription)
                    ->setHelp('Traditional help')
                    ->addArgument('t1')
                    ->addOption('t2')
                ;
            }
        };

        if (DocblockConfiguration::supportsLazy()) {
            // Symfony <5.3 does not have this feature
            $this->assertSame('Traditional description', $command::getDefaultDescription());
        }

        $this->assertSame('traditional:name', $command::getDefaultName());
        $this->assertSame('Traditional description', $command->getDescription());
        $this->assertSame('Traditional help', $command->getHelp());
        $this->assertTrue($command->getDefinition()->hasArgument('t1'));
        $this->assertTrue($command->getDefinition()->hasOption('t2'));
        $this->assertFalse($command->getDefinition()->hasArgument('arg'));
        $this->assertFalse($command->getDefinition()->hasOption('option'));
    }

    /**
     * @test
     */
    public function malformed_argument(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Argument tag "@argument foo==bar" on "');
        $this->expectExceptionMessage('" is malformed.');

        /**
         * @command some:command
         *
         * @argument foo==bar
         */
        new class() extends DocblockCommand {};
    }

    /**
     * @test
     */
    public function malformed_option(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Option tag "@option foo==bar" on "');
        $this->expectExceptionMessage('" is malformed.');

        /**
         * @command some:command
         *
         * @option foo==bar
         */
        new class() extends DocblockCommand {};
    }

    /**
     * @test
     */
    public function only_one_command_tag_is_allowed(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('"@command" tag can only be used once in "');

        /**
         * @command first
         * @command second
         */
        new class() extends DocblockCommand {};
    }

    /**
     * @test
     */
    public function can_add_arguments_and_options_to_command_tag(): void
    {
        /**
         * @command some:command arg1 ?arg2 arg3=default arg4="default with space" ?arg5[] --option1 --option2= --option3=default --option4="default with space" --o|option5[]
         */
        $command = new class() extends DocblockCommand {};
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
        $this->expectExceptionMessage('"@command" tag must have a value in "');

        /**
         * @command
         */
        new class() extends DocblockCommand {};
    }

    /**
     * @test
     */
    public function malformed_command_tag_argument(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('"@command" tag has a malformed argument ("foo==bar") in "');

        /**
         * @command my:command foo==bar
         */
        new class() extends DocblockCommand {};
    }

    /**
     * @test
     */
    public function malformed_command_tag_option(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('"@command" tag has a malformed option ("--foo==bar") in "');

        /**
         * @command my:command --foo==bar
         */
        new class() extends DocblockCommand {};
    }

    /**
     * @test
     */
    public function can_mark_as_hidden_with_hidden_tag(): void
    {
        /**
         * @command some:command
         */
        $command = new class() extends DocblockCommand {};

        $this->assertFalse($command->isHidden());

        /**
         * @command my:command
         * @hidden
         */
        $command = new class() extends DocblockCommand {};

        $this->assertTrue($command->isHidden());
        $this->assertSame('my:command', $command->getName());
    }

    /**
     * @test
     */
    public function hidden_command_is_lazy(): void
    {
        /**
         * @command my:command
         * @hidden
         */
        $command = new class() extends DocblockCommand {};

        if (DocblockConfiguration::supportsLazy()) {
            // Symfony 5.3+ supports setting hidden lazily
            $this->assertSame('|my:command', $command::getDefaultName());
        } else {
            $this->assertSame('my:command', $command::getDefaultName());
        }
    }

    /**
     * @test
     */
    public function can_add_aliases_with_alias_tag(): void
    {
        /**
         * @command aliased:command
         * @alias alias1
         * @alias alias2
         */
        $command = new class() extends DocblockCommand {};

        $this->assertSame('aliased:command', $command->getName());
        $this->assertSame(['alias1', 'alias2'], $command->getAliases());
    }

    /**
     * @test
     */
    public function aliased_command_is_lazy(): void
    {
        /**
         * @command aliased:command
         * @alias alias1
         * @alias alias2
         */
        $command = new class() extends DocblockCommand {};

        if (DocblockConfiguration::supportsLazy()) {
            // Symfony 5.3+ supports lazy aliases
            $this->assertSame('aliased:command|alias1|alias2', $command::getDefaultName());
        } else {
            $this->assertSame('aliased:command', $command::getDefaultName());
        }
    }

    /**
     * @test
     */
    public function fully_condensed_command_tag(): void
    {
        /**
         * @command |kitchen:sink|alias1|alias2 arg --option
         */
        $command = new class() extends DocblockCommand {};

        if (DocblockConfiguration::supportsLazy()) {
            // Symfony 5.3+ supports lazy
            $this->assertSame('|kitchen:sink|alias1|alias2', $command::getDefaultName());
        } else {
            $this->assertSame('kitchen:sink', $command::getDefaultName());
        }

        $this->assertSame('kitchen:sink', $command->getName());
        $this->assertTrue($command->isHidden());
        $this->assertSame(['alias1', 'alias2'], $command->getAliases());

        $definition = $command->getDefinition();

        $arg = $definition->getArgument('arg');
        $this->assertTrue($arg->isRequired());
        $this->assertFalse($arg->isArray());
        $this->assertSame('', $arg->getDescription());
        $this->assertNull($arg->getDefault());

        $option = $definition->getOption('option');
        $this->assertFalse($option->isArray());
        $this->assertFalse($option->getDefault());
        $this->assertSame('', $option->getDescription());
        $this->assertNull($option->getShortcut());
        $this->assertFalse($option->isValueRequired());
    }

    /**
     * @test
     */
    public function auto_name_if_missing_command_tag(): void
    {
        $command = new AutoNameDocblockCommand();

        $this->assertStringContainsString('app:', $command::getDefaultName());
        $this->assertStringContainsString('app:', $command->getName());
    }
}
