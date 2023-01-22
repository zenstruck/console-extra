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

use Zenstruck\Console\Attribute\Argument;
use Zenstruck\Console\Attribute\Option;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait ConfigureWithAttributes
{
    protected function configure(): void
    {
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
