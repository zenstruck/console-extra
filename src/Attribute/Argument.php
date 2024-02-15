<?php

/*
 * This file is part of the zenstruck/console-extra package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Console\Attribute;

use Symfony\Component\Console\Input\InputArgument;

use function Symfony\Component\String\s;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_PARAMETER | \Attribute::IS_REPEATABLE)]
class Argument
{
    /**
     * @see InputArgument::__construct()
     */
    public function __construct(
        public ?string $name = null,
        private ?int $mode = null,
        private string $description = '',
        private string|bool|int|float|array|null $default = null,
    ) {
    }

    /**
     * @internal
     *
     * @return mixed[]|null
     */
    final public static function parseParameter(\ReflectionParameter $parameter): ?array
    {
        if (!$attributes = $parameter->getAttributes(self::class, \ReflectionAttribute::IS_INSTANCEOF)) {
            return null;
        }

        if (\count($attributes) > 1) {
            throw new \LogicException(\sprintf('%s cannot be repeated when used as a parameter attribute.', self::class));
        }

        /** @var self $value */
        $value = $attributes[0]->newInstance();

        if (!$value->name && $parameter->name !== s($parameter->name)->snake()->replace('_', '-')->toString()) {
            trigger_deprecation('zenstruck/console-extra', '1.4', 'Argument names will default to kebab-case in 2.0. Specify the name in #[Argument] explicitly to remove this deprecation.');
        }

        $value->name ??= $parameter->name;

        if ($value->mode) {
            throw new \LogicException(\sprintf('Cannot use $mode when using %s as a parameter attribute, this is inferred from the parameter\'s type.', self::class));
        }

        if ($value->default) {
            throw new \LogicException(\sprintf('Cannot use $default when using %s as a parameter attribute, this is inferred from the parameter\'s default value.', self::class));
        }

        $value->mode = $parameter->isDefaultValueAvailable() || $parameter->allowsNull() ? InputArgument::OPTIONAL : InputArgument::REQUIRED;

        if ($parameter->getType() instanceof \ReflectionNamedType && 'array' === $parameter->getType()->getName()) {
            $value->mode |= InputArgument::IS_ARRAY;
        }

        if ($parameter->isDefaultValueAvailable()) {
            $value->default = $parameter->getDefaultValue();
        }

        return $value->values();
    }

    /**
     * @internal
     *
     * @return mixed[]
     */
    final public function values(): array
    {
        if (!$this->name) {
            throw new \LogicException(\sprintf('A $name is required when using %s as a command class attribute.', self::class));
        }

        return [$this->name, $this->mode, $this->description, $this->default];
    }
}
