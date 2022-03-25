<?php

namespace Zenstruck\Console\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Log\Logger;
use Zenstruck\Console\IO;
use Zenstruck\Console\Test\InteractsWithConsole;
use Zenstruck\Console\Test\TestInput;
use Zenstruck\Console\Test\TestOutput;
use Zenstruck\Console\Tests\Fixture\Command\ServiceCommand;
use Zenstruck\Console\Tests\Fixture\Command\ServiceSubscriberTraitCommand;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class InvokableServiceCommandTest extends KernelTestCase
{
    use InteractsWithConsole;

    /**
     * @test
     */
    public function can_auto_inject_services_into_invoke(): void
    {
        $this->executeConsoleCommand(ServiceCommand::class)
            ->assertSuccessful()
            ->assertOutputContains(\sprintf('IO: %s', IO::class))
            ->assertOutputContains(\sprintf('InputInterface: %s', TestInput::class))
            ->assertOutputContains(\sprintf('OutputInterface: %s', TestOutput::class))
            ->assertOutputContains(\sprintf('StyleInterface: %s', IO::class))
            ->assertOutputContains(\sprintf('none: %s', IO::class))
            ->assertOutputContains(\sprintf('LoggerInterface: %s', Logger::class))
            ->assertOutputContains(\sprintf('RouterInterface: %s', Router::class))
            ->assertOutputContains('Table: null')
            ->assertOutputContains('Parameter environment: test')
        ;
    }

    /**
     * @test
     */
    public function can_use_with_service_subscriber_trait(): void
    {
        $this->executeConsoleCommand(ServiceSubscriberTraitCommand::class)
            ->assertSuccessful()
            ->assertOutputContains(\sprintf('IO: %s', IO::class))
            ->assertOutputContains(\sprintf('LoggerInterface: %s', Logger::class))
            ->assertOutputContains(\sprintf('RouterInterface: %s', Router::class))
        ;
    }
}
