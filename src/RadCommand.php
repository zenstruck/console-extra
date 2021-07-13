<?php

namespace Zenstruck;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlockFactory;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Zenstruck\Callback\Parameter;
use Zenstruck\Callback\ValueFactory;
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
     * the command class. Command aliases can be added with the @alias
     * tag.
     *
     * 1. Use @command tag (ie @command app:user:report) for the command
     *    name. If there are multiple, subsequent tags are used as aliases.
     * 2. If no @command tag, use the class name to auto-generate the
     *    command name (ie GenerateUserReportCommand => app:generate-user-report).
     * 3. @alias tags (ie @alias user-report) add aliases for the command.
     */
    public static function getDefaultName(): string
    {
        if ($name = parent::getDefaultName()) {
            return $name;
        }

        if (($docblock = self::classDocblock())->hasTag('command')) {
            $name = \implode('|', $docblock->getTagsByName('command'));
        } else {
            $name = u((new \ReflectionClass(static::class))->getShortName())
                ->snake()
                ->replace('_', '-')
                ->beforeLast('-command')
                ->prepend('app:')
                ->toString()
            ;
        }

        return \implode('|', \array_merge([$name], $docblock->getTagsByName('alias')));
    }

    /**
     * Uses the command class' docblock "summary" to auto-generate
     * the command description.
     */
    public static function getDefaultDescription(): ?string
    {
        if (\method_exists(Command::class, 'getDefaultDescription') && $description = parent::getDefaultDescription()) {
            return $description;
        }

        $summary = self::classDocblock()->getSummary();
        $description = u($summary)->replace("\n", ' ');

        return $description->isEmpty() ? null : $description->toString();
    }

    public static function getSubscribedServices(): array
    {
        return self::invokeServices();
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
     */
    public function getHelp(): string
    {
        return parent::getHelp() ?: self::classDocblock()->getDescription()->render();
    }

    public function getDescription(): string
    {
        return parent::getDescription() ?: (string) self::getDefaultDescription();
    }

    /**
     * Use command class' docblock @argument/@option tags to auto-configure
     * options/arguments.
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
        $docblock = self::classDocblock();

        foreach ($docblock->getTagsByName('argument') as $tag) {
            $this->addArgument(...self::parseArgumentTag($tag));
        }

        foreach ($docblock->getTagsByName('option') as $tag) {
            $this->addOption(...self::parseOptionTag($tag));
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!\is_callable($this)) {
            throw new \LogicException(\sprintf('"%s" must implement __invoke() to use %s.', static::class, self::class));
        }

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
                        throw new \LogicException(\sprintf('"%s::__invoke()" cannot accept built-in parameter: %s (%s).', static::class, $parameter->getName(), $name));
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

    private static function parseArgumentTag(Tag $tag): array
    {
        if (\preg_match('#^(\?)?([\w\-]+)(=([\w\-]+))?(\s+(.+))?$#', $tag, $matches)) {
            $default = $matches[4] ?? null;

            return [
                $matches[2], // name
                $matches[1] || $default ? InputArgument::OPTIONAL : InputArgument::REQUIRED, // mode
                $matches[6] ?? '', // description
                $default ?: null, // default
            ];
        }

        // try matching with quoted default
        if (\preg_match('#^([\w\-]+)\=["\'](.+)["\'](\s+(.+))?$#', $tag, $matches)) {
            return [
                $matches[1], // name
                InputArgument::OPTIONAL, // mode
                $matches[4] ?? '', // description
                $matches[2], // default
            ];
        }

        // try matching array argument
        if (\preg_match('#^(\?)?([\w\-]+)\[\](\s+(.+))?$#', $tag, $matches)) {
            return [
                $matches[2], // name
                InputArgument::IS_ARRAY | ($matches[1] ? InputArgument::OPTIONAL : InputArgument::REQUIRED), // mode
                $matches[4] ?? '', // description
            ];
        }

        throw new \LogicException(\sprintf('Argument tag "%s" on "%s" is malformed.', $tag->render(), static::class));
    }

    private static function parseOptionTag(Tag $tag): array
    {
        if (\preg_match('#^(([\w\-]+)\|)?([\w\-]+)(=([\w\-]+)?)?(\s+(.+))?$#', $tag, $matches)) {
            $default = $matches[5] ?? null;

            return [
                $matches[3], // name
                $matches[2] ?: null, // shortcut
                $matches[4] ?? null ? InputOption::VALUE_REQUIRED : InputOption::VALUE_NONE, // mode
                $matches[7] ?? '', // description
                $default ?: null, // default
            ];
        }

        // try matching with quoted default
        if (\preg_match('#^(([\w\-]+)\|)?([\w\-]+)=["\'](.+)["\'](\s+(.+))?$#', $tag, $matches)) {
            return [
                $matches[3], // name
                $matches[2] ?: null, // shortcut
                InputOption::VALUE_REQUIRED, // mode
                $matches[6] ?? '', // description
                $matches[4], // default
            ];
        }

        // try matching array option
        if (\preg_match('#^(([\w\-]+)\|)?([\w\-]+)\[\](\s+(.+))?$#', $tag, $matches)) {
            return [
                $matches[3], // name
                $matches[2] ?: null, // shortcut
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, // mode
                $matches[5] ?? '', // description
            ];
        }

        throw new \LogicException(\sprintf('Option tag "%s" on "%s" is malformed.', $tag->render(), static::class));
    }

    private static function classDocblock(): DocBlock
    {
        return DocBlockFactory::createInstance()->create((new \ReflectionClass(static::class))->getDocComment());
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
