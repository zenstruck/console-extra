<?php

namespace Zenstruck\Console\Attribute;

use Symfony\Component\Console\Input\InputArgument;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final class Argument
{
    /**
     * @see InputArgument::__construct()
     */
    public function __construct(
        private string $name,
        private ?int $mode = null,
        private string $description = '',
        private string|bool|int|float|array|null $default = null,
    ) {
    }

    /**
     * @internal
     *
     * @return mixed[]
     */
    public function values(): array
    {
        return [$this->name, $this->mode, $this->description, $this->default];
    }
}
