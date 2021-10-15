<?php

namespace Zenstruck\RadCommand\Configuration;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Zenstruck\RadCommand\Configuration;
use function Symfony\Component\String\u;

/**
 * @internal
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DocblockConfiguration extends Configuration
{
    private static ?DocBlockFactory $factory;

    private DocBlock $docblock;
    private array $command;

    public static function isSupported(): bool
    {
        return \class_exists(DocBlock::class);
    }

    public function name(): ?string
    {
        return $this->command()[0] ?? null;
    }

    public function description(): ?string
    {
        return u($this->docblock()->getSummary())->replace("\n", ' ')->toString() ?: null;
    }

    public function help(): ?string
    {
        return $this->docblock()->getDescription() ?: null;
    }

    public function arguments(): iterable
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
                throw new \LogicException(\sprintf('"@command" tag has a malformed argument ("%s") in "%s".', $item, $this->class()->name));
            }
        }

        // parse @argument tags
        foreach ($this->docblock()->getTagsByName('argument') as $tag) {
            try {
                yield self::parseArgument($tag);
            } catch (\LogicException $e) {
                throw new \LogicException(\sprintf('Argument tag "%s" on "%s" is malformed.', $tag->render(), $this->class()->name));
            }
        }
    }

    public function options(): iterable
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
                throw new \LogicException(\sprintf('"@command" tag has a malformed option ("%s") in "%s".', $item, $this->class()->name));
            }
        }

        // parse @option tags
        foreach ($this->docblock()->getTagsByName('option') as $tag) {
            try {
                yield self::parseOption($tag);
            } catch (\LogicException $e) {
                throw new \LogicException(\sprintf('Option tag "%s" on "%s" is malformed.', $tag->render(), $this->class()->name));
            }
        }
    }

    private function command(): array
    {
        if (isset($this->command)) {
            return $this->command;
        }

        if (empty($tags = $this->docblock()->getTagsByName('command'))) {
            return $this->command = [];
        }

        if (\count($tags) > 1) {
            throw new \LogicException(\sprintf('"@command" tag can only be used once in "%s".', $this->class()->name));
        }

        if (!\preg_match_all('#[\w:?\-|=\[\]]+("[^"]*")?#', $tags[0], $matches)) {
            throw new \LogicException(\sprintf('"@command" tag must have a value in "%s".', $this->class()->name));
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
        if (\preg_match('#^([\w\-]+)=["\'](.+)["\'](\s+(.+))?$#', $value, $matches)) {
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
        if (\preg_match('#^(([\w\-]+)\|)?([\w\-]+)=["\'](.+)["\'](\s+(.+))?$#', $value, $matches)) {
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

    private function docblock(): DocBlock
    {
        return $this->docblock ??= self::factory()->create($this->class()->getDocComment());
    }
}
