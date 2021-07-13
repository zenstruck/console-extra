<?php

namespace Zenstruck\RadCommand\Tests\Fixture\Command;

use Zenstruck\RadCommand\IO;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CustomIO extends IO
{
    public function success($message): void
    {
        parent::success('Override Success');
    }
}
