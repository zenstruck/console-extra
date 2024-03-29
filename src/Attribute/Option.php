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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Input\InputOption;

use function Symfony\Component\String\s;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_PARAMETER | \Attribute::IS_REPEATABLE)]
class Option
{
    /**
     * @see InputOption::__construct()
     *
     * @param string[]|string $suggestions
     */
    public function __construct(
        public ?string $name = null,
        private string|array|null $shortcut = null,
        private ?int $mode = null,
        private string $description = '',
        private string|bool|int|float|array|null $default = null,
        private array|string $suggestions = [],
    ) {
    }

    /**
     * @internal
     *
     * @return mixed[]|null
     */
    final public static function parseParameter(\ReflectionParameter $parameter, Command $command): ?array
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
            trigger_deprecation('zenstruck/console-extra', '1.4', 'Argument names will default to kebab-case in 2.0. Specify the name in #[Option] explicitly to remove this deprecation.');
        }

        $value->name ??= $parameter->name;

        if ($value->mode) {
            throw new \LogicException(\sprintf('Cannot use $mode when using %s as a parameter attribute, this is inferred from the parameter\'s type.', self::class));
        }

        if ($value->default) {
            throw new \LogicException(\sprintf('Cannot use $default when using %s as a parameter attribute, this is inferred from the parameter\'s default value.', self::class));
        }

        $name = $parameter->getType() instanceof \ReflectionNamedType ? $parameter->getType()->getName() : null;

        $value->mode = match ($name) {
            'array' => InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
            'bool' => $parameter->allowsNull() ? InputOption::VALUE_NEGATABLE : InputOption::VALUE_NONE,
            default => InputOption::VALUE_REQUIRED,
        };

        if ($value->mode ^ InputOption::VALUE_NONE && $parameter->isDefaultValueAvailable()) {
            $value->default = $parameter->getDefaultValue();
        }

        return $value->values($command);
    }

    /**
     * @internal
     *
     * @return mixed[]
     */
    final public function values(Command $command): array
    {
        if (!$this->name) {
            throw new \LogicException(\sprintf('A $name is required when using %s as a command class attribute.', self::class));
        }

        $suggestions = $this->suggestions;

        if (\is_string($suggestions)) {
            $suggestions = \Closure::bind(
                fn(CompletionInput $i) => $this->{$suggestions}($i),
                $command,
                $command,
            );
        }

        return [$this->name, $this->shortcut, $this->mode, $this->description, $this->default, $suggestions];
    }
}
