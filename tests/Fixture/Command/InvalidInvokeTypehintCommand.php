<?php

namespace Zenstruck\RadCommand\Tests\Fixture\Command;

use Zenstruck\RadCommand;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class InvalidInvokeTypehintCommand extends RadCommand
{
    public function __invoke(string $invalid)
    {
    }
}
