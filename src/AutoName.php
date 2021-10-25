<?php

namespace Zenstruck\Console;

use function Symfony\Component\String\u;

/**
 * Uses the class name to auto-generate the command name (with "app:" prefix).
 *
 * @example GenerateUserReportCommand => app:generate-user-report
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait AutoName
{
    public static function getDefaultName(): string
    {
        if ($name = parent::getDefaultName()) {
            return $name;
        }

        $class = new \ReflectionClass(static::class);

        if ($class->isAnonymous()) {
            throw new \LogicException(\sprintf('Using "%s" with an anonymous class is not supported.', __TRAIT__));
        }

        return u($class->getShortName())
            ->snake()
            ->replace('_', '-')
            ->beforeLast('-command')
            ->prepend('app:')
            ->toString()
        ;
    }
}
