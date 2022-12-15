<?php

/*
 * This file is part of the zenstruck/console-extra package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
