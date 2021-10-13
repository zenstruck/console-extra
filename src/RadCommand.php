<?php

namespace Zenstruck;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Zenstruck\Callback\Parameter;
use Zenstruck\Callback\ValueFactory;
use Zenstruck\RadCommand\CommandDocblock;
use Zenstruck\RadCommand\IO;
use function Symfony\Component\String\u;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class RadCommand extends Command implements ServiceSubscriberInterface
{
    private ?ContainerInterface $container = null;

    /** @var array<?string, callable> */
    private static array $argumentFactories = [];

    /**
     * Auto-generates the command name from either a @command tag or
     * the command class.
     *
     * 1. Use @command tag (ie @command app:user:report) for the command
     *    name.
     * 2. If no @command tag, use the class name to auto-generate the
     *    command name (ie GenerateUserReportCommand => app:generate-user-report).
     *
     * You can opt-out of this behaviour by setting your command name
     * in one of the traditional ways:
     *
     * 1. Set the {@see Command::$defaultName} property.
     * 2. Override the {@see Command::getDefaultName()} method.
     * 3. Use the {@see AsCommand} attribute.
     */
    public static function getDefaultName(): string
    {
        if ($name = parent::getDefaultName()) {
            return $name;
        }

        return self::docblock()->name() ?? u((new \ReflectionClass(static::class))->getShortName())
            ->snake()
            ->replace('_', '-')
            ->beforeLast('-command')
            ->prepend('app:')
            ->toString()
        ;
    }

    /**
     * Uses the command class' docblock "summary" to auto-generate
     * the command description.
     *
     * You can opt-out of this behaviour by setting your command name
     * in one of the traditional ways:
     *
     * 1. Set the {@see Command::$defaultDescription)} property.
     * 2. Override {@see Command::getDefaultDescription()} method.
     * 3. Use the {@see AsCommand} attribute.
     */
    public static function getDefaultDescription(): ?string
    {
        if (\method_exists(Command::class, 'getDefaultDescription') && $description = parent::getDefaultDescription()) {
            return $description;
        }

        return self::docblock()->description();
    }

    public static function getSubscribedServices(): array
    {
        return \array_values(self::invokeServices());
    }

    /**
     * Add a custom __invoke() argument factory (or override defaults).
     *
     * Defaults are:
     * 1. null (no typehint): new IO()
     * 2. InputInterface: new IO()
     * 3. OutputInterface: new IO()
     * 4. StyleInterface: new IO()
     *
     * @param array|string|null $type    The argument type (use null for untyped)
     *                                   Pass an array of types to have them all
     *                                   created with the $factory. An interface
     *                                   or base class may be used
     * @param callable          $factory The factory to create the argument instance
     *                                   Callable arguments are:
     *                                   1. InputInterface: $input passed to execute()
     *                                   2. OutputInterface: $output passed to execute()
     *                                   3. ?string: the actual argument's type-hint
     */
    final public static function addArgumentFactory($type, callable $factory): void
    {
        foreach ((array) $type as $t) {
            self::$argumentFactories[$t] = $factory;
        }
    }

    /**
     * @required
     *
     * @return void Left off as a "real" return type to ensure compatible
     *              with ServiceSubscriberTrait
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Uses the command class' docblock "description" to auto-generate
     * the command help.
     *
     * Opt-out by configuring your command's help in the traditional
     * way.
     */
    public function getHelp(): string
    {
        return parent::getHelp() ?: self::docblock()->help();
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

    /**
     * Use command class' docblock @argument/@option tags to auto-configure
     * options/arguments.
     *
     * Opt-out of this behaviour by overriding this method and configuring
     * your options/arguments in the traditional way.
     *
     * @argument argument-name Argument Description
     * @option option-name Option Description
     *
     * @argument arg required
     * @argument ?arg optional
     * @argument arg[] required array
     * @argument ?arg[] optional array
     * @argument arg=foo with default
     * @argument arg="foo bar" quote default to include spaces
     *
     * @option option no value required
     * @option option= value required
     * @option option=foo with default
     * @option option="foo bar" quote default to include spaces
     * @option option[] array
     * @option o|option with shortcut (prefix any of the above with "<shortcut>|")
     */
    protected function configure(): void
    {
        $docblock = self::docblock();

        foreach ($docblock->arguments() as $argument) {
            $this->addArgument(...$argument);
        }

        foreach ($docblock->options() as $option) {
            $this->addOption(...$option);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $parameters = self::invokeArgumentFactories($input, $output);

        foreach (self::invokeServices() as $service) {
            $optional = 0 === \mb_strpos($service, '?');
            $parameters[] = Parameter::typed(
                \ltrim($service, '?'),
                new ValueFactory(function(string $service) use ($optional) {
                    try {
                        return $this->container()->get($service);
                    } catch (NotFoundExceptionInterface $e) {
                        if (!$optional) {
                            throw $e;
                        }
                    }

                    return null;
                })
            );
        }

        $return = Callback::createFor($this)->invokeAll(Parameter::union(...$parameters));

        if (null === $return) {
            $return = 0; // assume 0
        }

        if (!\is_int($return)) {
            throw new \LogicException(\sprintf('"%s::__invoke()" must return void|null|int. Got "%s".', static::class, get_debug_type($return)));
        }

        return $return;
    }

    private static function invokeServices(): array
    {
        try {
            $invoke = (new \ReflectionClass(static::class))->getMethod('__invoke');
        } catch (\ReflectionException $e) {
            throw new \LogicException(\sprintf('"%s" must implement __invoke() to use %s.', static::class, self::class), 0, $e);
        }

        return \array_filter(
            \array_map(
                static function(\ReflectionParameter $parameter): ?string {
                    if (!$type = $parameter->getType()) {
                        return null;
                    }

                    if (!$type instanceof \ReflectionNamedType) {
                        return null;
                    }

                    $name = $type->getName();

                    if ($type->isBuiltin()) {
                        throw new \LogicException(\sprintf('"%s::__invoke()" cannot accept built-in parameter: $%s (%s).', static::class, $parameter->getName(), $name));
                    }

                    if (\is_a($name, InputInterface::class, true)) {
                        return null;
                    }

                    if (\is_a($name, OutputInterface::class, true)) {
                        return null;
                    }

                    if (\is_a($name, StyleInterface::class, true)) {
                        return null;
                    }

                    return $type->allowsNull() ? '?'.$name : $name;
                },
                $invoke->getParameters()
            )
        );
    }

    private function container(): ContainerInterface
    {
        if (!$this->container) {
            throw new \LogicException(\sprintf('Container not available in "%s", is this class auto-configured?', static::class));
        }

        return $this->container;
    }

    private static function docblock(): CommandDocblock
    {
        return new CommandDocblock(static::class);
    }

    /**
     * @return Parameter[]
     */
    private static function invokeArgumentFactories(InputInterface $input, OutputInterface $output): array
    {
        $factories = \array_merge(
            \array_fill_keys(IO::SUPPORTED_TYPES, static fn() => new IO($input, $output)),
            self::$argumentFactories
        );

        return \array_map(
            static function(?string $type, callable $factory) use ($input, $output) {
                $valueFactory = new ValueFactory(static fn(?string $type) => $factory($input, $output, $type));

                return $type ? Parameter::typed($type, $valueFactory) : Parameter::untyped($valueFactory);
            },
            \array_keys($factories),
            $factories
        );
    }
}
