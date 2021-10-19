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
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        static::invokeMethod();

        $io = new IO($input, $output);

        $parameters = \array_merge(
            [
                Parameter::untyped($io),
                Parameter::typed(InputInterface::class, $input, Argument::EXACT),
                Parameter::typed(OutputInterface::class, $output, Argument::EXACT),
                Parameter::typed(IO::class, $io, Argument::COVARIANCE),
                Parameter::typed(IO::class, Parameter::factory(fn($class) => new $class($input, $output))),
            ],
            $this->invokeParameters()
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
     */
    private static function invokeMethod(): \ReflectionMethod
    {
        try {
            return (new \ReflectionClass(static::class))->getMethod('__invoke');
        } catch (\ReflectionException $e) {
            throw new \LogicException(\sprintf('"%s" must implement __invoke() to use %s.', static::class, Invokable::class));
        }
    }

    /**
     * @internal
     *
     * @return array<Parameter>
     */
    private function invokeParameters(): array
    {
        return [];
    }
}
