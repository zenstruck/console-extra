<?php

namespace Zenstruck\RadCommand;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class IO extends SymfonyStyle implements InputInterface
{
    public const SUPPORTED_TYPES = [
        null,
        StyleInterface::class,
        InputInterface::class,
        OutputInterface::class,
    ];

    private InputInterface $input;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        parent::__construct($input, $output);

        $this->input = $input;
    }

    /**
     * Helper for {@see ProgressBar::iterate()}.
     */
    public function progressIterate(iterable $iterable, ?int $max = null): iterable
    {
        yield from $this->createProgressBar()->iterate($iterable, $max);

        $this->newLine(2);
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
