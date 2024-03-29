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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Zenstruck\Console\Attribute\Argument;
use Zenstruck\Console\Attribute\Option;
use Zenstruck\Console\ConfigureWithAttributes;
use Zenstruck\Console\InvokableCommand;
use Zenstruck\Console\Test\TestCommand;
use Zenstruck\Console\Tests\Fixture\Attribute\CustomArgument;
use Zenstruck\Console\Tests\Fixture\Attribute\CustomOption;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ConfigureWithAttributesTest extends TestCase
{
    /**
     * @test
     * @dataProvider attributeCommandProvider
     */
    public function parse_arguments_and_options(string $class): void
    {
        $command = new $class();
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

    public static function attributeCommandProvider(): iterable
    {
        yield [WithClassAttributesCommand::class];
        yield [WithParameterAttributesCommand::class];
    }

    /**
     * @test
     */
    public function argument_attribute_name_required_when_using_on_class(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('A $name is required when using %s as a command class attribute.', Argument::class));

        new ArgumentClassAttributeMissingName();
    }

    /**
     * @test
     */
    public function option_attribute_name_required_when_using_on_class(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('A $name is required when using %s as a command class attribute.', Option::class));

        new OptionClassAttributeMissingName();
    }

    /**
     * @test
     */
    public function negatable_parameter_attribute_options(): void
    {
        $command = TestCommand::for(
            new class('command') extends InvokableCommand {
                public function __invoke(
                    #[Option] ?bool $foo,
                ): void {
                    $this->io()->writeln(\sprintf('foo: %s', \var_export($foo, true)));
                }
            },
        );

        $command->execute()
            ->assertSuccessful()
            ->assertOutputContains('foo: NULL')
        ;

        $command->execute('--foo')
            ->assertSuccessful()
            ->assertOutputContains('foo: true')
        ;

        $command->execute('--no-foo')
            ->assertSuccessful()
            ->assertOutputContains('foo: false')
        ;
    }

    /**
     * @test
     */
    public function can_customize_argument_and_option_names_via_parameter_attribute(): void
    {
        $command = TestCommand::for(
            new class('command') extends InvokableCommand {
                public function __invoke(
                    #[Argument('custom-foo')] ?string $foo,
                    #[Option('custom-bar')] bool $bar,
                ): void {
                    $this->io()->writeln(\sprintf('foo: %s', \var_export($foo, true)));
                    $this->io()->writeln(\sprintf('bar: %s', \var_export($bar, true)));
                }
            },
        );

        $command->execute()
            ->assertSuccessful()
            ->assertOutputContains('foo: NULL')
            ->assertOutputContains('bar: false')
        ;

        $command->execute('value --custom-bar')
            ->assertSuccessful()
            ->assertOutputContains("foo: 'value'")
            ->assertOutputContains('bar: true')
        ;
    }

    /**
     * @test
     */
    public function cannot_use_mode_with_argument_as_parameter_attribute(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('Cannot use $mode when using %s as a parameter attribute, this is inferred from the parameter\'s type.', Argument::class));

        new class('command') extends InvokableCommand {
            public function __invoke(
                #[Argument(mode: InputArgument::REQUIRED)] $foo,
            ): void {
            }
        };
    }

    /**
     * @test
     */
    public function cannot_use_default_with_argument_as_parameter_attribute(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('Cannot use $default when using %s as a parameter attribute, this is inferred from the parameter\'s default value.', Argument::class));

        new class('command') extends InvokableCommand {
            public function __invoke(
                #[Argument(default: true)] $foo,
            ): void {
            }
        };
    }

    /**
     * @test
     */
    public function cannot_use_mode_with_option_as_parameter_attribute(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('Cannot use $mode when using %s as a parameter attribute, this is inferred from the parameter\'s type.', Option::class));

        new class('command') extends InvokableCommand {
            public function __invoke(
                #[Option(mode: InputArgument::REQUIRED)] $foo,
            ): void {
            }
        };
    }

    /**
     * @test
     */
    public function cannot_use_default_with_option_as_parameter_attribute(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('Cannot use $default when using %s as a parameter attribute, this is inferred from the parameter\'s default value.', Option::class));

        new class('command') extends InvokableCommand {
            public function __invoke(
                #[Option(default: true)] $foo,
            ): void {
            }
        };
    }

    /**
     * @test
     */
    public function option_not_repeatable_when_used_on_parameter(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('%s cannot be repeated when used as a parameter attribute.', Option::class));

        new class('command') extends InvokableCommand {
            public function __invoke(
                #[Option]
                #[Option]
                $foo,
            ): void {
            }
        };
    }

    /**
     * @test
     */
    public function argument_not_repeatable_when_used_on_parameter(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('%s cannot be repeated when used as a parameter attribute.', Argument::class));

        new class('command') extends InvokableCommand {
            public function __invoke(
                #[Argument]
                #[Argument]
                $foo,
            ): void {
            }
        };
    }

    /**
     * @test
     * @group legacy
     */
    public function kebab_case_argument_deprecation(): void
    {
        $command = TestCommand::for(
            new class('command') extends InvokableCommand {
                public function __invoke(
                    #[Argument] string $fooBar,
                ): void {
                    $this->io()->comment('fooBar: '.$fooBar);
                }
            },
        );

        $command->execute('value')
            ->assertSuccessful()
            ->assertOutputContains('fooBar: value')
        ;
    }

    /**
     * @test
     * @group legacy
     */
    public function kebab_case_option_deprecation(): void
    {
        $command = TestCommand::for(
            new class('command') extends InvokableCommand {
                public function __invoke(
                    #[Option] string $fooBar,
                ): void {
                    $this->io()->comment('fooBar: '.$fooBar);
                }
            },
        );

        $command->execute('--fooBar=value')
            ->assertSuccessful()
            ->assertOutputContains('fooBar: value')
        ;
    }

    /**
     * @test
     * @group legacy
     */
    public function direct_user_to_remove_trait_if_not_required(): void
    {
        TestCommand::for(
            new class('command') extends InvokableCommand {
                use ConfigureWithAttributes;

                public function __invoke()
                {
                }
            })
            ->execute()
            ->assertSuccessful()
        ;
    }

    /**
     * @test
     */
    public function can_use_custom_argument_and_option_attributes(): void
    {
        $command = new class('command') extends Command {
            use ConfigureWithAttributes;

            public function __invoke(
                #[CustomArgument('arg description')] string $foo,
                #[CustomOption('opt description')] bool $bar,
            ) {
            }
        };

        $this->assertSame('arg description', $command->getDefinition()->getArgument('foo')->getDescription());
        $this->assertSame('opt description', $command->getDefinition()->getOption('bar')->getDescription());
    }

    /**
     * @test
     */
    public function configure_argument_suggestions(): void
    {
        $command = TestCommand::for(
            new class('command') extends Command {
                use ConfigureWithAttributes;

                public function __invoke(
                    #[Argument(suggestions: ['foo', 'bar'])]
                    string $arg1,

                    #[Argument(suggestions: 'arg2Suggestions')]
                    string $arg2,
                ): void {
                }

                private function arg2Suggestions(CompletionInput $input): array
                {
                    return ['baz', 'qux'];
                }
            },
        );

        $command
            ->complete('')->is(['foo', 'bar'])->back()
            ->complete('first ')->is(['baz', 'qux'])->back()
        ;
    }

    /**
     * @test
     */
    public function configure_option_suggestions(): void
    {
        $command = TestCommand::for(
            new class('command') extends Command {
                use ConfigureWithAttributes;

                public function __invoke(
                    #[Option(suggestions: ['foo', 'bar'])]
                    string $first,

                    #[Option(suggestions: 'secondSuggestions')]
                    string $second,
                ): void {
                }

                private function secondSuggestions(CompletionInput $input): array
                {
                    return ['baz', 'qux'];
                }
            },
        );

        $command
            ->complete('--first=')->is(['foo', 'bar'])->back()
            ->complete('--second=')->is(['baz', 'qux'])->back()
        ;
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
class WithClassAttributesCommand extends Command
{
    use ConfigureWithAttributes;
}

class WithParameterAttributesCommand extends InvokableCommand
{
    public function __invoke(
        #[Argument(description: 'First argument is required')]
        string $arg1,

        #[Argument(description: 'Second argument is optional')]
        ?string $arg2 = null,

        #[Argument(description: 'Third argument is optional with a default value')]
        string $arg3 = 'default',

        #[Argument('arg4', description: 'Fourth argument is an optional array')]
        array $foo = [],

        #[Option(description: 'First option (no value)')]
        bool $option1 = false,

        #[Option(description: 'Second option (value required)')]
        ?string $option2 = null,

        #[Option(description: 'Third option with default value')]
        string $option3 = 'default',

        #[Option('option4', shortcut: 'o', description: 'Fourth option is an array with a shortcut (-o)')]
        array $bar = [],
    ) {
    }
}

#[Argument]
class ArgumentClassAttributeMissingName extends Command
{
    use ConfigureWithAttributes;
}

#[Option]
class OptionClassAttributeMissingName extends Command
{
    use ConfigureWithAttributes;
}
