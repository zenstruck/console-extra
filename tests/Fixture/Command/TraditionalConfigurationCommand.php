<?php

namespace Zenstruck\RadCommand\Tests\Fixture\Command;

use Zenstruck\RadCommand;

/**
 * Not used description.
 *
 * Not used help.
 *
 * @command not:used:name
 * @argument arg1 not used
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class TraditionalConfigurationCommand extends RadCommand
{
    protected static $defaultName = 'traditional:name';
    protected static $defaultDescription = 'Traditional description';

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->setHelp('Traditional help')
            ->addArgument('t1')
            ->addOption('t2')
        ;
    }
}
