<?php

namespace Zenstruck\Console;

use Zenstruck\Console\Attribute\Argument;
use Zenstruck\Console\Attribute\Option;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait ConfigureWithAttributes
{
    protected function configure(): void
    {
        if (\PHP_VERSION_ID < 80000) {
            throw new \LogicException(\sprintf('PHP 8+ required to use %s.', ConfigureWithAttributes::class));
        }

        $class = new \ReflectionClass($this);

        foreach ($class->getAttributes(Argument::class) as $attribute) {
            $this->addArgument(...$attribute->newInstance()->values());
        }

        foreach ($class->getAttributes(Option::class) as $attribute) {
            $this->addOption(...$attribute->newInstance()->values());
        }

        try {
            $parameters = (new \ReflectionClass(static::class))->getMethod('__invoke')->getParameters();
        } catch (\ReflectionException $e) {
            return; // not using Invokable
        }

        foreach ($parameters as $parameter) {
            if ($args = Argument::parseParameter($parameter)) {
                $this->addArgument(...$args);

                continue;
            }

            if ($args = Option::parseParameter($parameter)) {
                $this->addOption(...$args);
            }
        }
    }
}
