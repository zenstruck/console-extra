<?php

namespace Zenstruck\RadCommand;

use Symfony\Component\Console\Command\Command;
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
    private static bool $supportsLazy;

    private \ReflectionClass $class;

    public function __construct(string $class)
    {
        $this->class = new \ReflectionClass($class);
    }

    final public static function create(string $class): self
    {
        return self::$instances[$class] ??= new ChainConfiguration($class);
    }

    public static function supportsLazy(): bool
    {
        // only 53+ has this method and therefore supports lazy hidden/aliases
        return self::$supportsLazy ??= \method_exists(Command::class, 'getDefaultDescription');
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
     * @return \Traversable<array>
     */
    public function arguments(): \Traversable
    {
        return new \EmptyIterator();
    }

    /**
     * @return \Traversable<array>
     */
    public function options(): \Traversable
    {
        return new \EmptyIterator();
    }

    public function hidden(): bool
    {
        return false;
    }

    /**
     * @return \Traversable<string>
     */
    public function aliases(): \Traversable
    {
        return new \EmptyIterator();
    }

    final protected function class(): \ReflectionClass
    {
        return $this->class;
    }
}
