<?php

namespace Zenstruck\RadCommand\Configuration;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use function Symfony\Component\String\u;

/**
 * @internal
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DocblockConfiguration
{
    /** @var array<class-string, self> */
    private static array $instances = [];
    private static DocBlockFactory $factory;
    private static bool $supportsLazy;

    private \ReflectionClass $class;
    private DocBlock $docblock;
    private array $command;

    private function __construct(string $class)
    {
        $this->class = new \ReflectionClass($class);
        $this->docblock = self::factory()->create($this->class->getDocComment() ?: ' '); // hack to allow empty docblock
    }

    public static function for(string $class): self
    {
        return self::$instances[$class] ??= new self($class);
    }

    public static function supportsLazy(): bool
    {
        // only 53+ has this method and therefore supports lazy hidden/aliases
        return self::$supportsLazy ??= \method_exists(Command::class, 'getDefaultDescription');
    }

    public function name(): ?string
    {
        $name = $this->command()[0] ?? null;

        if (!$name || self::supportsLazy()) {
            // in 5.3+ let Symfony handle lazy aliases/hidden syntax
            return $name;
        }

        return \explode('|', \ltrim($name, '|'))[0];
    }

    public function description(): ?string
    {
        return u($this->docblock->getSummary())->replace("\n", ' ')->toString() ?: null;
    }

    public function help(): ?string
    {
        return $this->docblock->getDescription() ?: null;
    }

    public function arguments(): \Traversable
    {
        $command = $this->command();

        \array_shift($command);

        // parse arguments from @command tag
        foreach ($command as $item) {
            if (u($item)->startsWith('--')) {
                continue;
            }

            try {
                yield self::parseArgument($item);
            } catch (\LogicException $e) {
                throw new \LogicException(\sprintf('"@command" tag has a malformed argument ("%s") in "%s".', $item, $this->class->name));
            }
        }

        // parse @argument tags
        foreach ($this->docblock->getTagsByName('argument') as $tag) {
            try {
                yield self::parseArgument($tag);
            } catch (\LogicException $e) {
                throw new \LogicException(\sprintf('Argument tag "%s" on "%s" is malformed.', $tag->render(), $this->class->name));
            }
        }
    }

    public function options(): \Traversable
    {
        $command = $this->command();

        \array_shift($command);

        // parse options from @command tag
        foreach ($command as $item) {
            $item = u($item);

            if (!$item->startsWith('--')) {
                continue;
            }

            try {
                yield self::parseOption($item->after('--'));
            } catch (\LogicException $e) {
                throw new \LogicException(\sprintf('"@command" tag has a malformed option ("%s") in "%s".', $item, $this->class->name));
            }
        }

        // parse @option tags
        foreach ($this->docblock->getTagsByName('option') as $tag) {
            try {
                yield self::parseOption($tag);
            } catch (\LogicException $e) {
                throw new \LogicException(\sprintf('Option tag "%s" on "%s" is malformed.', $tag->render(), $this->class->name));
            }
        }
    }

    public function hidden(): bool
    {
        if ($this->docblock->hasTag('hidden')) {
            return true;
        }

        // in <5.3 if command name starts with "|", mark as lazy (ie "|my:command")
        return !self::supportsLazy() && u($this->command()[0] ?? '')->startsWith('|');
    }

    public function aliases(): \Traversable
    {
        foreach ($this->docblock->getTagsByName('alias') as $alias) {
            yield (string) $alias;
        }

        if (self::supportsLazy()) {
            // in 5.3+, let Symfony handle alias syntax
            return;
        }

        // parse aliases from command name (ie "my:command|alias1|alias2")
        $aliases = \explode('|', \ltrim($this->command()[0] ?? '', '|'));

        \array_shift($aliases);

        foreach (\array_filter($aliases) as $alias) {
            yield $alias;
        }
    }

    private function command(): array
    {
        if (isset($this->command)) {
            return $this->command;
        }

        if (empty($tags = $this->docblock->getTagsByName('command'))) {
            return $this->command = [];
        }

        if (\count($tags) > 1) {
            throw new \LogicException(\sprintf('"@command" tag can only be used once in "%s".', $this->class->name));
        }

        if (!\preg_match_all('#[\w:?\-|=\[\]]+("[^"]*")?#', $tags[0], $matches)) {
            throw new \LogicException(\sprintf('"@command" tag must have a value in "%s".', $this->class->name));
        }

        return $this->command = $matches[0];
    }

    private static function parseArgument(string $value): array
    {
        if (\preg_match('#^(\?)?([\w\-]+)(=([\w\-]+))?(\s+(.+))?$#', $value, $matches)) {
            $default = $matches[4] ?? null;

            return [
                $matches[2], // name
                $matches[1] || $default ? InputArgument::OPTIONAL : InputArgument::REQUIRED, // mode
                $matches[6] ?? '', // description
                $default ?: null, // default
            ];
        }

        // try matching with quoted default
        if (\preg_match('#^([\w\-]+)="([^"]*)"(\s+(.+))?$#', $value, $matches)) {
            return [
                $matches[1], // name
                InputArgument::OPTIONAL, // mode
                $matches[4] ?? '', // description
                $matches[2], // default
            ];
        }

        // try matching array argument
        if (\preg_match('#^(\?)?([\w\-]+)\[\](\s+(.+))?$#', $value, $matches)) {
            return [
                $matches[2], // name
                InputArgument::IS_ARRAY | ($matches[1] ? InputArgument::OPTIONAL : InputArgument::REQUIRED), // mode
                $matches[4] ?? '', // description
            ];
        }

        throw new \LogicException(\sprintf('Malformed argument: "%s".', $value));
    }

    private static function parseOption(string $value): array
    {
        if (\preg_match('#^(([\w\-]+)\|)?([\w\-]+)(=([\w\-]+)?)?(\s+(.+))?$#', $value, $matches)) {
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
        if (\preg_match('#^(([\w\-]+)\|)?([\w\-]+)="([^"]*)"(\s+(.+))?$#', $value, $matches)) {
            return [
                $matches[3], // name
                $matches[2] ?: null, // shortcut
                InputOption::VALUE_REQUIRED, // mode
                $matches[6] ?? '', // description
                $matches[4], // default
            ];
        }

        // try matching array option
        if (\preg_match('#^(([\w\-]+)\|)?([\w\-]+)\[\](\s+(.+))?$#', $value, $matches)) {
            return [
                $matches[3], // name
                $matches[2] ?: null, // shortcut
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, // mode
                $matches[5] ?? '', // description
            ];
        }

        throw new \LogicException(\sprintf('Malformed option: "%s".', $value));
    }

    private static function factory(): DocBlockFactory
    {
        return self::$factory ??= DocBlockFactory::createInstance();
    }
}
