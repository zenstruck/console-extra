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

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * SymfonyStyle implementation that is also an implementation of
 * {@see InputInterface} to help simplify commands.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class IO extends SymfonyStyle implements InputInterface
{
    private InputInterface $input;
    private OutputInterface $output;

    final public function __construct(InputInterface $input, OutputInterface $output)
    {
        parent::__construct($this->input = $input, $this->output = $output);
    }

    public function __toString(): string
    {
        return $this->input->__toString();
    }

    public function input(): InputInterface
    {
        return $this->input;
    }

    public function output(): OutputInterface
    {
        return $this->output;
    }

    /**
     * Override to ensure an instance of IO is returned.
     */
    public function getErrorStyle(): self
    {
        return new static($this->input, $this->getErrorOutput());
    }

    /**
     * Alias for {@see getArgument()}.
     */
    public function argument(string $name): mixed
    {
        return $this->getArgument($name);
    }

    /**
     * Alias for {@see getOption()}.
     */
    public function option(string $name): mixed
    {
        return $this->getOption($name);
    }

    public function getFirstArgument(): ?string
    {
        return $this->input->getFirstArgument();
    }

    /**
     * @param string|string[] $values
     * @param bool            $onlyParams
     */
    public function hasParameterOption($values, $onlyParams = false): bool
    {
        return $this->input->hasParameterOption($values, $onlyParams);
    }

    /**
     * @param string|string[] $values
     * @param mixed           $default
     * @param bool            $onlyParams
     */
    public function getParameterOption($values, $default = false, $onlyParams = false): mixed
    {
        return $this->input->getParameterOption($values, $default, $onlyParams);
    }

    public function bind(InputDefinition $definition): void
    {
        $this->input->bind($definition);
    }

    public function validate(): void
    {
        $this->input->validate();
    }

    /**
     * @return array<mixed>
     */
    public function getArguments(): array
    {
        return $this->input->getArguments();
    }

    /**
     * @param string $name
     */
    public function getArgument($name): mixed
    {
        return $this->input->getArgument($name);
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function setArgument($name, $value): void
    {
        $this->input->setArgument($name, $value);
    }

    /**
     * @param string $name
     */
    public function hasArgument($name): bool
    {
        return $this->input->hasArgument($name);
    }

    /**
     * @return array<mixed>
     */
    public function getOptions(): array
    {
        return $this->input->getOptions();
    }

    /**
     * @param string $name
     */
    public function getOption($name): mixed
    {
        return $this->input->getOption($name);
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function setOption($name, $value): void
    {
        $this->input->setOption($name, $value);
    }

    /**
     * @param string $name
     */
    public function hasOption($name): bool
    {
        return $this->input->hasOption($name);
    }

    public function isInteractive(): bool
    {
        return $this->input->isInteractive();
    }

    /**
     * @param bool $interactive
     */
    public function setInteractive($interactive): void
    {
        $this->input->setInteractive($interactive);
    }
}
