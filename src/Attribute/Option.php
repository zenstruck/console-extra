<?php

namespace Zenstruck\Console\Attribute;

use Symfony\Component\Console\Input\InputOption;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final class Option
{
    /**
     * @see InputOption::__construct()
     */
    public function __construct(
        private string $name,
        private string|array|null $shortcut = null,
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
        return [$this->name, $this->shortcut, $this->mode, $this->description, $this->default];
    }
}
