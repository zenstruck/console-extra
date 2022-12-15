<?php

namespace Zenstruck\Console\Tests\Fixture\Command;

use Symfony\Component\Console\Command\Command;
use Zenstruck\Console\AutoName;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class AutoNameNoPrefixCommand extends Command
{
    use AutoName;

    protected static function autoNamePrefix(): string
    {
        return '';
    }
}
