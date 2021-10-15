<?php

namespace Zenstruck\RadCommand\Configuration;

use Zenstruck\RadCommand\Configuration;

/**
 * @internal
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ChainConfiguration extends Configuration
{
    /** @var iterable<Configuration> */
    private iterable $configurations = [];

    public function __construct(string $class)
    {
        if (DocblockConfiguration::isSupported()) {
            $this->configurations[] = new DocblockConfiguration($class);
        }

        $this->configurations[] = new AutoNameConfiguration($class);
    }

    public function name(): ?string
    {
        foreach ($this->configurations as $configuration) {
            if ($name = $configuration->name()) {
                return $name;
            }
        }

        return null;
    }

    public function description(): ?string
    {
        foreach ($this->configurations as $configuration) {
            if ($description = $configuration->description()) {
                return $description;
            }
        }

        return null;
    }

    public function help(): ?string
    {
        foreach ($this->configurations as $configuration) {
            if ($help = $configuration->help()) {
                return $help;
            }
        }

        return null;
    }

    public function arguments(): iterable
    {
        foreach ($this->configurations as $configuration) {
            yield from $configuration->arguments();
        }
    }

    public function options(): iterable
    {
        foreach ($this->configurations as $configuration) {
            yield from $configuration->options();
        }
    }

    public function hidden(): bool
    {
        foreach ($this->configurations as $configuration) {
            if ($configuration->hidden()) {
                return true;
            }
        }

        return false;
    }
}
