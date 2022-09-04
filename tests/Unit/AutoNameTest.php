<?php

namespace Zenstruck\Console\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Zenstruck\Console\AutoName;
use Zenstruck\Console\Tests\Fixture\Command\AutoNameCommand;
use Zenstruck\Console\Tests\Fixture\Command\AutoNameNoPrefixCommand;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
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
     *
     * @group legacy
     */
    public function can_use_traditional_naming_method(): void
    {
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
