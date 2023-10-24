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
use Zenstruck\Console\AutoName;
use Zenstruck\Console\Tests\Fixture\Command\AutoNameCommand;
use Zenstruck\Console\Tests\Fixture\Command\AutoNameNoPrefixCommand;
use Zenstruck\Console\Tests\Fixture\Kernel;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @group legacy
 */
final class AutoNameTest extends TestCase
{
    /**
     * @test
     */
    public function generates_name_automatically(): void
    {
        $this->assertSame('app:auto-name', AutoNameCommand::getDefaultName());
        $this->assertSame('app:auto-name', (new AutoNameCommand())->getName());
    }

    /**
     * @test
     */
    public function can_remove_prefix(): void
    {
        $this->assertSame('auto-name-no-prefix', AutoNameNoPrefixCommand::getDefaultName());
        $this->assertSame('auto-name-no-prefix', (new AutoNameNoPrefixCommand())->getName());
    }

    /**
     * @test
     */
    public function can_use_traditional_naming_method(): void
    {
        if (Kernel::MAJOR_VERSION > 6) {
            $this->markTestSkipped();
        }

        $command = new class() extends Command {
            use AutoName;

            protected static $defaultName = 'override';
        };

        $this->assertSame('override', $command::getDefaultName());
        $this->assertSame('override', $command->getName());
    }

    /**
     * @test
     */
    public function using_auto_name_with_anonymous_class_is_not_supported(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('Using "%s" with an anonymous class is not supported.', AutoName::class));

        new class() extends Command {
            use AutoName;
        };
    }
}
