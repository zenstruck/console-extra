<?php

namespace Zenstruck\RadCommand\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Zenstruck\RadCommand\Tests\Fixture\Command\AutoNameCommand;

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
    public function can_use_traditional_naming_method(): void
    {
        $command = new class() extends AutoNameCommand {
            protected static $defaultName = 'override';
        };

        $this->assertSame('override', $command::getDefaultName());
        $this->assertSame('override', $command->getName());
    }
}
