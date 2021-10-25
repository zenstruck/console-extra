<?php

namespace Zenstruck\Console\Tests\Fixture\Command;

use Symfony\Component\Console\Command\Command;
use Zenstruck\Console\AutoName;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class AutoNameCommand extends Command
{
    use AutoName;
}
