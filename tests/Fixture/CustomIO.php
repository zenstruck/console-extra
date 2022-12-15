<?php

namespace Zenstruck\Console\Tests\Fixture;

use Zenstruck\Console\IO;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CustomIO extends IO
{
    public function success($message): void
    {
        parent::success('OVERRIDE');
    }
}
