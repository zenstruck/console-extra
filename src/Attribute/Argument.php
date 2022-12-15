<?php

namespace Zenstruck\Console\Attribute;

use Symfony\Component\Console\Input\InputArgument;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_PARAMETER | \Attribute::IS_REPEATABLE)]
final class Argument
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
    public static function parseParameter(\ReflectionParameter $parameter): ?array
    {
        if (!$attributes = $parameter->getAttributes(self::class)) {
            return null;
        }

        if (\count($attributes) > 1) {
            throw new \LogicException(\sprintf('%s cannot be repeated when used as a parameter attribute.', self::class));
        }

        /** @var self $value */
        $value = $attributes[0]->newInstance();
        $value->name = $value->name ?? $parameter->name;

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
    public function values(): array
    {
        if (!$this->name) {
            throw new \LogicException(\sprintf('A $name is required when using %s as a command class attribute.', self::class));
        }

        return [$this->name, $this->mode, $this->description, $this->default];
    }
}
