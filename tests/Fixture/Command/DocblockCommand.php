<?php

namespace Zenstruck\Console\Tests\Fixture\Command;

use Symfony\Component\Console\Command\Command;
use Zenstruck\Console\ConfigureWithDocblocks;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class DocblockCommand extends Command
{
    use ConfigureWithDocblocks;
}
