<?php

/*
 * This file is part of the zenstruck/console-extra package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Console\Tests\Fixture\Attribute;

use Zenstruck\Console\Attribute\Argument;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_PARAMETER | \Attribute::IS_REPEATABLE)]
final class CustomArgument extends Argument
{
    public function __construct(
        string $description = '',
        ?string $name = null,
        ?int $mode = null,
        float|int|bool|array|string|null $default = null,
    ) {
        parent::__construct($name, $mode, $description, $default);
    }
}
