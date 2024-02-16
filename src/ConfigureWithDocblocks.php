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

use Zenstruck\Console\Configuration\DocblockConfiguration;

/**
 * Fully configure your command using docblock annotations.
 *
 * - Uses the command class' docblock "summary" to auto-generate
 *   the command description.
 * - Uses the command class' docblock "description" to auto-generate
 *   the command help.
 * - @command tag to set the command name and optionally arguments/options
 * - If no @command tag, uses {@see AutoName}
 * - @hidden tag to mark the command as "hidden"
 * - @alias tags to add command aliases
 * - @argument tag to add command arguments
 * - @option tag to add command options
 *
 * Examples:
 *
 * @command app:my:command
 * @alias alias1
 * @alias alias2
 * @hidden
 *
 * @argument arg1 First argument is required
 * @argument ?arg2 Second argument is optional
 * @argument arg3=default Third argument is optional with a default value
 * @argument arg4="default with space" Forth argument is "optional" with a default value (with spaces)
 * @argument ?arg5[] Fifth argument is an optional array
 *
 * @option option1 First option (no value)
 * @option option2= Second option (value required)
 * @option option3=default Third option with default value
 * @option option4="default with space" Forth option with "default" value (with spaces)
 * @option o|option5[] Fifth option is an array with a shortcut (-o)
 *
 * You can pack all the above into a single @command tag. It is
 * recommended to only do this for very simple commands as it isn't
 * as explicit as splitting the tags out.
 *
 * @command |app:my:command|alias1|alias2 arg1 ?arg2 arg3=default arg4="default with space" ?arg5[] --option1 --option2= --option3=default --option4="default with space" --o|option5[]
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @deprecated
 */
trait ConfigureWithDocblocks
{
    use AutoName { getDefaultName as autoDefaultName; }

    public static function getDefaultName(): string
    {
        $name = parent::getDefaultName() ?: self::docblock()->name() ?: self::autoDefaultName();

        if (!DocblockConfiguration::supportsLazy()) {
            return $name;
        }

        if ('|' !== $name[0] && self::docblock()->hidden()) {
            $name = '|'.$name;
        }

        return \implode('|', \array_merge([$name], \iterator_to_array(self::docblock()->aliases())));
    }

    public static function getDefaultDescription(): ?string
    {
        if (\method_exists(parent::class, 'getDefaultDescription') && $description = parent::getDefaultDescription()) {
            return $description;
        }

        return self::docblock()->description();
    }

    /**
     * Required to auto-generate the command description from the
     * docblock in symfony/console < 5.3.
     *
     * @see getDefaultDescription()
     */
    public function getDescription(): string
    {
        return parent::getDescription() ?: (string) self::getDefaultDescription();
    }

    public function getHelp(): string
    {
        return parent::getHelp() ?: (string) self::docblock()->help();
    }

    protected function configure(): void
    {
        foreach (self::docblock()->arguments() as $argument) {
            $this->addArgument(...$argument);
        }

        foreach (self::docblock()->options() as $option) {
            $this->addOption(...$option);
        }

        if (!DocblockConfiguration::supportsLazy() && self::docblock()->hidden()) {
            $this->setHidden(true);
        }

        if (!DocblockConfiguration::supportsLazy()) {
            $this->setAliases(\iterator_to_array(self::docblock()->aliases()));
        }
    }

    /**
     * @internal
     *
     * @return DocblockConfiguration<static>
     */
    private static function docblock(): DocblockConfiguration
    {
        return DocblockConfiguration::for(static::class);
    }
}
