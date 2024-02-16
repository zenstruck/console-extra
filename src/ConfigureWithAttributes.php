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
        if (InvokableCommand::class !== self::class && $this instanceof InvokableCommand) { // @phpstan-ignore-line
            trigger_deprecation('zenstruck/console-extra', '1.4', 'You can safely remove "%s" from "%s".', __TRAIT__, $this::class);
        }

        $class = new \ReflectionClass($this);

        foreach ($class->getAttributes(Argument::class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            $this->addArgument(...$attribute->newInstance()->values($this));
        }

        foreach ($class->getAttributes(Option::class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            $this->addOption(...$attribute->newInstance()->values($this));
        }

        try {
            $parameters = (new \ReflectionClass(static::class))->getMethod('__invoke')->getParameters();
        } catch (\ReflectionException) {
            return; // not using Invokable
        }

        foreach ($parameters as $parameter) {
            if ($args = Argument::parseParameter($parameter, $this)) {
                $this->addArgument(...$args);

                continue;
            }

            if ($args = Option::parseParameter($parameter, $this)) {
                $this->addOption(...$args);
            }
        }
    }
}
