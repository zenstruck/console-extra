<?php

/*
 * This file is part of the zenstruck/console-extra package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Console\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Log\Logger;
use Zenstruck\Console\IO;
use Zenstruck\Console\Test\InteractsWithConsole;
use Zenstruck\Console\Test\TestInput;
use Zenstruck\Console\Test\TestOutput;
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
        $this->executeConsoleCommand('service-command foo bar --opt2')
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
            ->assertOutputContains("arg1: 'foo'")
            ->assertOutputContains("arg2: 'bar'")
            ->assertOutputContains("env: 'test'")
            ->assertOutputContains('opt1: false')
            ->assertOutputContains('opt2: true')
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
