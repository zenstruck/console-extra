<?php

namespace Zenstruck\RadCommand;

use Zenstruck\RadCommand\Configuration\ChainConfiguration;

/**
 * @internal
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class Configuration
{
    /** @var array<class-string, static> */
    private static array $instances = [];

    private \ReflectionClass $class;

    public function __construct(string $class)
    {
        $this->class = new \ReflectionClass($class);
    }

    final public static function create(string $class): self
    {
        return self::$instances[$class] ??= new ChainConfiguration($class);
    }

    public function name(): ?string
    {
        return null;
    }

    public function description(): ?string
    {
        return null;
    }

    public function help(): ?string
    {
        return null;
    }

    /**
     * @return iterable<array>
     */
    public function arguments(): iterable
    {
        return [];
    }

    /**
     * @return iterable<array>
     */
    public function options(): iterable
    {
        return [];
    }

    final protected function class(): \ReflectionClass
    {
        return $this->class;
    }
}
