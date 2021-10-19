<?php

namespace Zenstruck\RadCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zenstruck\Callback;
use Zenstruck\Callback\Argument;
use Zenstruck\Callback\Parameter;

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

    /**
     * @param callable(InputInterface,OutputInterface):mixed $factory
     */
    public function addArgumentFactory(?string $type, callable $factory): self
    {
        $this->argumentFactories[$type] = $factory;

        return $this;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        static::invokeParameters();

        $io = ($this->argumentFactories[IO::class] ?? static fn() => new IO($input, $output))($input, $output);

        $parameters = \array_merge(
            \array_map(
                static function(callable $factory, ?string $type) use ($input, $output) {
                    $factory = Parameter::factory(fn() => $factory($input, $output));

                    return $type ? Parameter::typed($type, $factory, Argument::EXACT) : Parameter::untyped($factory);
                },
                $this->argumentFactories,
                \array_keys($this->argumentFactories)
            ),
            [
                Parameter::untyped($io),
                Parameter::typed(InputInterface::class, $input, Argument::EXACT),
                Parameter::typed(OutputInterface::class, $output, Argument::EXACT),
                Parameter::typed(IO::class, $io, Argument::COVARIANCE),
                Parameter::typed(IO::class, Parameter::factory(fn($class) => new $class($input, $output))),
            ]
        );

        $return = Callback::createFor($this)->invokeAll(Parameter::union(...$parameters));

        if (null === $return) {
            $return = 0; // assume 0
        }

        if (!\is_int($return)) {
            throw new \LogicException(\sprintf('"%s::__invoke()" must return void|null|int. Got "%s".', static::class, get_debug_type($return)));
        }

        return $return;
    }

    /**
     * @internal
     *
     * @return array<\ReflectionParameter>
     */
    private static function invokeParameters(): array
    {
        try {
            return (new \ReflectionClass(static::class))->getMethod('__invoke')->getParameters();
        } catch (\ReflectionException $e) {
            throw new \LogicException(\sprintf('"%s" must implement __invoke() to use %s.', static::class, Invokable::class));
        }
    }
}
