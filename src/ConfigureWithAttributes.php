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
        $class = new \ReflectionClass($this);

        foreach ($class->getAttributes(Argument::class) as $attribute) {
            $this->addArgument(...$attribute->newInstance()->values());
        }

        foreach ($class->getAttributes(Option::class) as $attribute) {
            $this->addOption(...$attribute->newInstance()->values());
        }
    }
}
