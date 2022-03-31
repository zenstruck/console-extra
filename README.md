# zenstruck/console-extra

[![CI Status](https://github.com/zenstruck/console-extra/workflows/CI/badge.svg)](https://github.com/zenstruck/console-extra/actions?query=workflow%3ACI)
[![codecov](https://codecov.io/gh/zenstruck/console-extra/branch/1.x/graph/badge.svg?token=OEFPA53TDM)](https://codecov.io/gh/zenstruck/console-extra)

A modular set of features to reduce configuration boilerplate for your commands:

```php
/**
 * Creates a user in the database.
 *
 * @command create:user email password --r|role[]
 */
final class CreateUserCommand extends InvokableServiceCommand
{
    use ConfigureWithDocblocks;

    public function __invoke(IO $io, UserRepository $repo): void
    {
        $repo->createUser($io->argument('email'), $io->argument('password'), $io->option('role'));

        $io->success('Created user.');
    }
}
```

```bash
bin/console create:user kbond p4ssw0rd -r ROLE_EDITOR -r ROLE_ADMIN

 [OK] Created user.

 // Duration: < 1 sec, Peak Memory: 10.0 MiB
```

## Installation

```bash
composer require zenstruck/console-extra
```

## Usage

This library is a set of modular features that can be used separately or in combination.

**TIP**: To reduce command boilerplate even further, it is recommended to create an abstract base command for your
app that enables all the features you desire. Then have all your app's commands extend this.

### `IO`

This is a helper object that extends `SymfonyStyle` and implements `InputInterface` (so it implements
`InputInterface`, `OutputInterface`, and `StyleInterface`).

```php
use Zenstruck\Console\IO;

$io = new IO($input, $output);

$io->getOption('role'); // InputInterface
$io->writeln('a line'); // OutputInterface
$io->success('Created.'); // StyleInterface

// additional methods
$io->input(); // get the "wrapped" input
$io->output(); // get the "wrapped" output
```

On its own, it isn't very special, but it can be auto-injected into [`Invokable`](#invokable) commands.

### `Invokable`

Use this trait to remove the need for extending `Command::execute()` and just inject what your need (ie [`IO`](#io))
into your command's `__invoke()` method.

```php
use Symfony\Component\Console\Command\Command;
use Zenstruck\Console\Invokable;
use Zenstruck\Console\IO;

class MyCommand extends \Symfony\Component\Console\Command\Command
{
    use Invokable;

    public function __invoke(IO $io)
    {
        $role = $io->option('role');

        $io->success('created.');

        // even if you don't inject IO, it's available as a method:
        $this->io(); // IO
    }
}
```

You can auto-inject the "raw" input/output:

```php
public function __invoke(IO $io, InputInterface $input, OutputInterface $output)
```

No return type (or `void`) implies a `0` status code. You can return an integer if you want to change this:

```php
public function __invoke(IO $io): int
{
    return $success ? 0 : 1;
}
```

### `InvokableServiceCommand`

If using the Symfony Framework, you can take [`Invokable`](#invokable) to the next level by auto-injecting services
into `__invoke()`. This allows your commands to behave like
[Invokable Service Controllers](https://symfony.com/doc/current/controller/service.html#invokable-controllers)
(with `controller.service_arguments`). Instead of a _Request_, you inject [`IO`](#io).

Have your commands extend `InvokableServiceCommand` and ensure they are auto-wired/configured.

```php
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Zenstruck\Console\InvokableServiceCommand;
use Zenstruck\Console\IO;

class CreateUserCommand extends InvokableServiceCommand
{
    public function __invoke(IO $io, UserRepository $repo, LoggerInterface $logger): void
    {
        // access container parameters
        $environment = $this->parameter('kernel.environment');

        // ...
    }
}
```

### `AutoName`

Use this trait to have your command's name auto-generated from the class name:

```php
use Symfony\Component\Console\Command\Command;
use Zenstruck\Console\AutoName;

class CreateUserCommand extends Command
{
    use AutoName; // command's name will be "app:create-user"
}
```

### `ConfigureWithDocblocks`

Use this trait to allow your command to be configured by your command class' docblock.
`phpdocumentor/reflection-docblock` is required for this feature
(`composer install phpdocumentor/reflection-docblock`).

**Example:**

```php
use Symfony\Component\Console\Command\Command;
use Zenstruck\Console\ConfigureWithDocblocks;

/**
 * This is the command's description.
 *
 * This is the command help
 *
 * Multiple
 *
 * lines allowed.
 *
 * @command my:command
 * @alias alias1
 * @alias alias2
 * @hidden
 *
 * @argument arg1 First argument is required (this is the argument's "description")
 * @argument ?arg2 Second argument is optional
 * @argument arg3=default Third argument is optional with a default value
 * @argument arg4="default with space" Forth argument is "optional" with a default value (with spaces)
 * @argument ?arg5[] Fifth argument is an optional array
 *
 * @option option1 First option (no value) (this is the option's "description")
 * @option option2= Second option (value required)
 * @option option3=default Third option with default value
 * @option option4="default with space" Forth option with "default" value (with spaces)
 * @option o|option5[] Fifth option is an array with a shortcut (-o)
 */
class MyCommand extends Command
{
    use ConfigureWithDocblocks;
}
```

**NOTES**:
1. If the `@command` tag is absent, [AutoName](#autoname) is used.
2. All the configuration can be disabled by using the traditional methods of configuring your command.
3. Command's are still [lazy](https://symfony.com/blog/new-in-symfony-3-4-lazy-commands) using this method of
   configuration but there is overhead in parsing the docblocks so be aware of this.

#### `@command` Tag

You can pack all the above into a single `@command` tag. This can act like _routing_ for your console:

```php
/**
 * @command |app:my:command|alias1|alias2 arg1 ?arg2 arg3=default arg4="default with space" ?arg5[] --option1 --option2= --option3=default --option4="default with space" --o|option5[]
 */
class MyCommand extends Command
{
    use ConfigureWithDocblocks;
}
```

**NOTES**:
1. The `|` prefix makes the command hidden.
2. Argument/Option descriptions are not allowed.

**TIP**: It is recommended to only do this for very simple commands as it isn't as explicit as splitting the tags out.

### `CommandSummarySubscriber`

Add this event subscriber to your `Application`'s event dispatcher to display a summary after every command is run.
The summary includes the duration of the command and peak memory usage.

If using Symfony, configure it as a service to enable:

```yaml
# config/packages/zenstruck_console_extra.yaml
Zenstruck\Console\EventListener\CommandSummarySubscriber:
    autoconfigure: true
```

**NOTE**: This will display a summary after every registered command runs.
