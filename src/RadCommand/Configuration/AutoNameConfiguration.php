<?php

namespace Zenstruck\RadCommand\Configuration;

use Zenstruck\RadCommand\Configuration;
use function Symfony\Component\String\u;

/**
 * @internal
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class AutoNameConfiguration extends Configuration
{
    public function name(): ?string
    {
        return u($this->class()->getShortName())
            ->snake()
            ->replace('_', '-')
            ->beforeLast('-command')
            ->prepend('app:')
            ->toString()
        ;
    }
}
