<?php

namespace Zenstruck\RadCommand\Tests\Fixture\Command;

use Symfony\Component\Console\Command\Command;
use Zenstruck\RadCommand\ConfigureWithDocblocks;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class DocblockCommand extends Command
{
    use ConfigureWithDocblocks;
}
