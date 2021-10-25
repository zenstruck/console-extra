<?php

namespace Zenstruck\Console;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
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

    /**
     * Helper for {@see ProgressBar::iterate()}.
     */
    public function progressIterate(iterable $iterable, ?int $max = null): iterable
    {
        if (\method_exists(parent::class, 'progressIterate')) {
            // SymfonyStyle 5.4+ includes this method
            yield from parent::progressIterate($iterable, $max);

            return;
        }

        yield from $this->createProgressBar()->iterate($iterable, $max);

        $this->newLine(2);
    }

    /**
     * Create a styled table. Uses {@see ConsoleSectionOutput} if available.
     */
    public function createTable(): Table
    {
        if (\method_exists(parent::class, 'createTable')) {
            // SymfonyStyle 5.4+ includes this method
            return parent::createTable();
        }

        $style = clone Table::getStyleDefinition('symfony-style-guide');
        $style->setCellHeaderFormat('<info>%s</info>');

        return (new Table($this->output instanceof ConsoleOutputInterface ? $this->output->section() : $this->output))
            ->setStyle($style)
        ;
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
    public function argument(string $name)
    {
        return $this->getArgument($name);
    }

    /**
     * Alias for {@see getOption()}.
     */
    public function option(string $name)
    {
        return $this->getOption($name);
    }

    public function getFirstArgument(): ?string
    {
        return $this->input->getFirstArgument();
    }

    public function hasParameterOption($values, $onlyParams = false): bool
    {
        return $this->input->hasParameterOption($values, $onlyParams);
    }

    /**
     * @return mixed
     */
    public function getParameterOption($values, $default = false, $onlyParams = false)
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

    public function getArguments(): array
    {
        return $this->input->getArguments();
    }

    /**
     * @return mixed
     */
    public function getArgument($name)
    {
        return $this->input->getArgument($name);
    }

    public function setArgument($name, $value): void
    {
        $this->input->setArgument($name, $value);
    }

    public function hasArgument($name): bool
    {
        return $this->input->hasArgument($name);
    }

    public function getOptions(): array
    {
        return $this->input->getOptions();
    }

    /**
     * @return mixed
     */
    public function getOption($name)
    {
        return $this->input->getOption($name);
    }

    public function setOption($name, $value): void
    {
        $this->input->setOption($name, $value);
    }

    public function hasOption($name): bool
    {
        return $this->input->hasOption($name);
    }

    public function isInteractive(): bool
    {
        return $this->input->isInteractive();
    }

    public function setInteractive($interactive): void
    {
        $this->input->setInteractive($interactive);
    }
}
