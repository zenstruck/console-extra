<?php

namespace Zenstruck\RadCommand;

use function Symfony\Component\String\u;

/**
 * Uses the class name to auto-generate the command name
 * (ie GenerateUserReportCommand => app:generate-user-report).
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

        return u((new \ReflectionClass(static::class))->getShortName())
            ->snake()
            ->replace('_', '-')
            ->beforeLast('-command')
            ->prepend('app:')
            ->toString()
        ;
    }
}
