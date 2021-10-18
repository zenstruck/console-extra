<?php

namespace Zenstruck\RadCommand\Tests\Fixture\Command;

use Symfony\Component\Console\Command\Command;
use Zenstruck\RadCommand\AutoName;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class AutoNameCommand extends Command
{
    use AutoName;
}
