<?php

namespace Zenstruck\RadCommand\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Zenstruck\Console\Test\TestCommand;
use Zenstruck\RadCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\CommandTagCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\CustomIO;
use Zenstruck\RadCommand\Tests\Fixture\Command\FullConfigurationCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\InjectableServicesCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\InvalidInvokeReturnCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\InvalidInvokeTypehintCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\InvokeReturnCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\MalformedArgumentCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\MalformedOptionCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\MissingInvokeCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\TraditionalConfigurationCommand;
use Zenstruck\RadCommand\Tests\Fixture\OptionalService;

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
            ->assertOutputContains('$empty: Zenstruck\RadCommand\IO')
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
            ->assertOutputContains('Done!')
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
    public function can_customize_the_io_factories(): void
    {
        RadCommand::addArgumentFactory(
            CustomIO::SUPPORTED_TYPES,
            static fn($input, $output) => new CustomIO($input, $output)
        );

        TestCommand::for(new FullConfigurationCommand())
            ->addArgument('value1')
            ->execute()
            ->assertSuccessful()
            ->assertOutputNotContains('Done!')
            ->assertOutputContains('Override Success')
        ;

        // reset
        $prop = (new \ReflectionClass(RadCommand::class))->getProperty('argumentFactories');
        $prop->setAccessible(true);
        $prop->setValue([]);
    }

    /**
     * @test
     */
    public function invoke_is_required_to_execute(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('"%s" must implement __invoke() to use Zenstruck\RadCommand.', MissingInvokeCommand::class));

        TestCommand::for(new MissingInvokeCommand())->execute();
    }

    /**
     * @test
     */
    public function invoke_is_required_to_get_subscribed_services(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('"%s" must implement __invoke() to use Zenstruck\RadCommand.', MissingInvokeCommand::class));

        MissingInvokeCommand::getSubscribedServices();
    }

    /**
     * @test
     */
    public function invoke_must_return_int_or_null(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('"%s::__invoke()" must return void|null|int. Got "string".', InvalidInvokeReturnCommand::class));

        TestCommand::for(new InvalidInvokeReturnCommand())->execute();
    }

    /**
     * @test
     */
    public function invoke_can_return_exit_code(): void
    {
        TestCommand::for(new InvokeReturnCommand())
            ->execute()
            ->assertStatusCode(1)
        ;
    }

    /**
     * @test
     */
    public function container_must_be_available_to_auto_inject_services(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('Container not available in "%s", is this class auto-configured?', InjectableServicesCommand::class));

        TestCommand::for(new InjectableServicesCommand())->execute();
    }

    /**
     * @test
     */
    public function get_subscribed_services_generated_from_invoke(): void
    {
        $this->assertSame(
            [
                LoggerInterface::class,
                '?'.OptionalService::class,
            ],
            InjectableServicesCommand::getSubscribedServices()
        );
    }

    /**
     * @test
     */
    public function command_can_use_traditional_configuration(): void
    {
        $this->assertSame('traditional:name', TraditionalConfigurationCommand::getDefaultName());

        if (\method_exists(Command::class, 'getDefaultDescription')) {
            // Symfony <5.3 does not have this feature
            $this->assertSame('Traditional description', TraditionalConfigurationCommand::getDefaultDescription());
        }

        $command = new TraditionalConfigurationCommand();

        $this->assertSame('traditional:name', $command->getName());
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
    public function cannot_use_built_in_typehint_for_invoke_argument(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('"%s::__invoke()" cannot accept built-in parameter: $invalid (string).', InvalidInvokeTypehintCommand::class));

        TestCommand::for(new InvalidInvokeTypehintCommand())->execute();
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
}
