<?php

namespace Zenstruck\RadCommand\Tests\Fixture\Command;

use Zenstruck\RadCommand;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class InvokeReturnCommand extends RadCommand
{
    public function __invoke()
    {
        return 1;
    }
}
