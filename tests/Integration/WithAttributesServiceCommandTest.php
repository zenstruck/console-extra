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

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Console\Test\InteractsWithConsole;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class WithAttributesServiceCommandTest extends KernelTestCase
{
    use InteractsWithConsole;

    /**
     * @test
     */
    public function services_injected(): void
    {
        $this->executeConsoleCommand('with-attributes-service-command foo bar')
            ->assertSuccessful()
            ->assertOutputContains('Imp1: implementation1')
            ->assertOutputContains('Imp2: implementation2')
            ->assertOutputContains('Env: test')
            ->assertOutputContains('Debug: true')
            ->assertOutputContains('Arg1: foo')
            ->assertOutputContains('Arg2: bar')
        ;
    }
}
