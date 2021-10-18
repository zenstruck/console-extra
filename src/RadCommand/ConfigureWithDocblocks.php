<?php

namespace Zenstruck\RadCommand;

use Zenstruck\RadCommand\Configuration\DocblockConfiguration;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
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
     */
    private static function docblock(): DocblockConfiguration
    {
        return DocblockConfiguration::for(static::class);
    }
}
