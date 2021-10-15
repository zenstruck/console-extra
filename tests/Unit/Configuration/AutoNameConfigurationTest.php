<?php

namespace Zenstruck\RadCommand\Tests\Unit\Configuration;

use PHPUnit\Framework\TestCase;
use Zenstruck\RadCommand\Tests\Fixture\Command\FullConfigurationCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\TraditionalConfigurationCommand;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class AutoNameConfigurationTest extends TestCase
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
    public function traditional_command_naming_supersedes_auto_name(): void
    {
        $this->assertSame('traditional:name', TraditionalConfigurationCommand::getDefaultName());
        $this->assertSame('traditional:name', (new TraditionalConfigurationCommand())->getName());
    }
}
