<?php

namespace Zenstruck\RadCommand\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Zenstruck\Console\Test\TestCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\CustomIOCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\FullConfigurationCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\InjectableServicesCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\InvalidInvokeReturnCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\InvalidInvokeTypehintCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\InvokeReturnCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\MissingInvokeCommand;
use Zenstruck\RadCommand\Tests\Fixture\OptionalService;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class RadCommandTest extends TestCase
{
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
        TestCommand::for(new CustomIOCommand())
            ->execute()
            ->assertSuccessful()
            ->assertOutputNotContains('Done!')
            ->assertOutputContains('Override Success')
        ;
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
    public function cannot_use_built_in_typehint_for_invoke_argument(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('"%s::__invoke()" cannot accept built-in parameter: $invalid (string).', InvalidInvokeTypehintCommand::class));

        TestCommand::for(new InvalidInvokeTypehintCommand())->execute();
    }
}
