<?php

/*
 * This file is part of the zenstruck/console-extra package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zenstruck\Callback;
use Zenstruck\Callback\Argument;
use Zenstruck\Callback\Parameter;
use Zenstruck\Console\Attribute\Argument as ConsoleArgument;
use Zenstruck\Console\Attribute\Option;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait Invokable
{
    /**
     * @internal
     *
     * @var array<string,callable>
     */
    private array $argumentFactories = [];

    private IO $io;

    /**
     * @param callable(InputInterface,OutputInterface):mixed $factory
     */
    public function addArgumentFactory(?string $type, callable $factory): self
    {
        $this->argumentFactories[$type] = $factory;

        return $this;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = ($this->argumentFactories[IO::class] ?? static fn() => new IO($input, $output))($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $parameters = \array_map(
            function(\ReflectionParameter $parameter) use ($input, $output) {
                $type = $parameter->getType();

                if (null !== $type && !$type instanceof \ReflectionNamedType) {
                    throw new \LogicException("Union/Intersection types not yet supported for \"{$parameter}\".");
                }

                if ($type instanceof \ReflectionNamedType && isset($this->argumentFactories[$type->getName()])) {
                    return Parameter::typed(
                        $type->getName(),
                        Parameter::factory(fn() => $this->argumentFactories[$type->getName()]($input, $output)),
                        Argument::EXACT,
                    );
                }

                if (isset($this->argumentFactories[$key = 'invoke:'.$parameter->name])) {
                    return $this->argumentFactories[$key]();
                }

                if (!$type || $type->isBuiltin()) {
                    $name = $parameter->name;

                    if ($attr = $parameter->getAttributes(ConsoleArgument::class)[0] ?? $parameter->getAttributes(Option::class)[0] ?? null) {
                        $name = $attr->newInstance()->name ?? $name;
                    }

                    if ($input->hasArgument($name)) {
                        return $input->getArgument($name);
                    }

                    if ($input->hasOption($name)) {
                        return $input->getOption($name);
                    }
                }

                return Parameter::union(
                    Parameter::untyped($this->io()),
                    Parameter::typed(InputInterface::class, $input, Argument::EXACT),
                    Parameter::typed(OutputInterface::class, $output, Argument::EXACT),
                    Parameter::typed(IO::class, $this->io(), Argument::COVARIANCE),
                    Parameter::typed(IO::class, Parameter::factory(fn($class) => new $class($input, $output))),
                );
            },
            self::invokeParameters(),
        );

        $return = Callback::createFor($this)->invoke(...$parameters); // @phpstan-ignore-line

        if (null === $return) {
            $return = 0; // assume 0
        }

        if (!\is_int($return)) {
            throw new \LogicException(\sprintf('"%s::__invoke()" must return void|null|int. Got "%s".', static::class, \get_debug_type($return)));
        }

        return $return;
    }

    protected function io(): IO
    {
        if (!isset($this->io)) {
            throw new \LogicException(\sprintf('Cannot call %s() before running command.', __METHOD__));
        }

        return $this->io;
    }

    /**
     * @internal
     *
     * @return array<\ReflectionParameter>
     */
    final protected static function invokeParameters(): array
    {
        try {
            return (new \ReflectionClass(static::class))->getMethod('__invoke')->getParameters();
        } catch (\ReflectionException $e) {
            throw new \LogicException(\sprintf('"%s" must implement __invoke() to use %s.', static::class, Invokable::class));
        }
    }
}
