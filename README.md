# zenstruck/console-extra

[![CI](https://github.com/zenstruck/console-extra/actions/workflows/ci.yml/badge.svg)](https://github.com/zenstruck/console-extra/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/zenstruck/console-extra/branch/1.x/graph/badge.svg?token=OEFPA53TDM)](https://codecov.io/gh/zenstruck/console-extra)

A modular set of features to reduce configuration boilerplate for your Symfony commands:

```php
#[AsCommand('create:user', 'Creates a user in the database.')]
final class CreateUserCommand extends InvokableServiceCommand
{
    use RunsCommands, RunsProcesses;

    public function __invoke(
        IO $io,

        UserManager $userManager,

        #[Argument]
        string $email,

        #[Argument]
        string $password,

        #[Option(name: 'role', shortcut: 'r')]
        array $roles,
    ): void {
        $userManager->createUser($email, $password, $roles);

        $this->runCommand('another:command');
        $this->runProcess('/some/script');

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

> [!NOTE]
> To reduce command boilerplate even further, it is recommended to create an abstract base command for your
> app that enables all the features you desire. Then have all your app's commands extend this.

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

### `InvokableCommand`

Extend this class to remove the need for extending `Command::execute()` and just inject what your need
into your command's `__invoke()` method. The following are parameters that can be auto-injected:

- [`Zenstruck\Console\IO`](#io)
- `Symfony\Component\Console\Style\StyleInterface`
- `Symfony\Component\Console\Input\InputInterface`
- `Symfony\Component\Console\Input\OutputInterface`
- *arguments* (parameter name must match argument name or use the `Zenstruck\Console\Attribute\Argument` attribute)
- *options* (parameter name must match option name or use the `Zenstruck\Console\Attribute\Option` attribute)

```php
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Zenstruck\Console\InvokableCommand;
use Zenstruck\Console\IO;

class MyCommand extends InvokableCommand
{
    // $username/$roles are the argument/option defined below
    public function __invoke(IO $io, string $username, array $roles)
    {
        $io->success('created.');

        // even if you don't inject IO, it's available as a method:
        $this->io(); // IO
    }

    public function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED)
            ->addOption('roles', mode: InputOption::VALUE_IS_ARRAY)
        ;
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

If using the Symfony Framework, you can take [`InvokableCommand`](#invokablecommand) to the next level by
auto-injecting services into `__invoke()`. This allows your commands to behave like
[Invokable Service Controllers](https://symfony.com/doc/current/controller/service.html#invokable-controllers)
(with `controller.service_arguments`). Instead of a _Request_, you inject [`IO`](#io).

Have your commands extend `InvokableServiceCommand` and ensure they are auto-wired/configured.

```php
use App\Service\UserManager;
use Psr\Log\LoggerInterface;
use Zenstruck\Console\InvokableServiceCommand;
use Zenstruck\Console\IO;

class CreateUserCommand extends InvokableServiceCommand
{
    public function __invoke(IO $io, UserManager $userManager, LoggerInterface $logger): void
    {
        // access container parameters
        $environment = $this->parameter('kernel.environment');

        // ...
    }
}
```

#### Inject with DI Attributes

You can use any
[DI attribute](https://symfony.com/doc/current/reference/attributes.html#dependency-injection) on
your `__invoke()` parameters:

```php
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Zenstruck\Console\InvokableServiceCommand;

class SomeCommand extends InvokableServiceCommand
{
    public function __invoke(
        #[Autowire('@some.service.id')]
        SomeService $service,

        #[Autowire('%kernel.environment%')]
        string $environment,

        #[Target('githubApi')]
        HttpClientInterface $httpClient,

        #[TaggedIterator('app.handler')]
        iterable $handlers,
    ): void {
        // ...
    }
}
```

### Configure with Attributes

Your commands that extend [`InvokableCommand`](#invokablecommand) or [`InvokableServiceCommand`](#invokableservicecommand)
can configure arguments and options with attributes:

```php
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Zenstruck\Console\Attribute\Argument;
use Zenstruck\Console\Attribute\Option;
use Zenstruck\Console\InvokableCommand;

#[Argument('arg1', description: 'Argument 1 description', mode: InputArgument::REQUIRED)]
#[Argument('arg2', description: 'Argument 1 description')]
#[Argument('arg3', suggestions: ['suggestion1', 'suggestion2'])] // for auto-completion
#[Argument('arg4', suggestions: 'suggestionsForArg4')] // use a method on the command to get suggestions
#[Option('option1', description: 'Option 1 description')]
#[Option('option2', suggestions: ['suggestion1', 'suggestion2'])] // for auto-completion
#[Option('option3', suggestions: 'suggestionsForOption3')] // use a method on the command to get suggestions
class MyCommand extends InvokableCommand
{
    // ...

    private function suggestionsForArg4(): array
    {
        return ['suggestion3', 'suggestion4'];
    }

    private function suggestionsForOption3(): array
    {
        return ['suggestion3', 'suggestion4'];
    }
}
```

#### Invokable Attributes

Instead of defining at the class level, you can add the `Option`/`Argument` attributes directly to your
`__invoke()` parameters to define _and_ inject arguments/options:

```php
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Zenstruck\Console\Attribute\Argument;
use Zenstruck\Console\Attribute\Option;
use Zenstruck\Console\InvokableCommand;

#[AsCommand('my:command')]
class MyCommand extends InvokableCommand
{
    public function __invoke(
        #[Argument]
        string $username, // defined as a required argument (username)

        #[Argument]
        string $password = 'p4ssw0rd', //  defined as an optional argument (password) with a default (p4ssw0rd)

        #[Option(name: 'role', shortcut: 'r', suggestions: ['ROLE_EDITOR', 'ROLE_REVIEWER'])]
        array $roles = [], // defined as an array option that requires values (--r|role[])

        #[Option(name: 'super-admin')]
        bool $superAdmin = false, // defined as a "value-less" option (--super-admin)

        #[Option]
        ?bool $force = null, // defined as a "negatable" option (--force/--no-force)

        #[Option]
        ?string $name = null, // defined as an option that requires a value (--name=)
    ): void {
        // ...
    }
}
```

> [!NOTE]
> Option/Argument _modes_ and _defaults_ are detected from the parameter's type-hint/default value
> and cannot be defined on the attribute.

### `CommandRunner`

A `CommandRunner` object is available to simplify running commands anywhere (ie controller):

```php
use Zenstruck\Console\CommandRunner;

/** @var \Symfony\Component\Console\Command\Command $command */

CommandRunner::for($command)->run(); // int (the status after running the command)

// pass arguments
CommandRunner::for($command, 'arg --opt')->run(); // int
```

If the application is available, you can use it to run commands:

```php
use Zenstruck\Console\CommandRunner;

/** @var \Symfony\Component\Console\Application $application */

CommandRunner::from($application, 'my:command')->run();

// pass arguments/options
CommandRunner::from($application, 'my:command arg --opt')->run(); // int
```

If your command is interactive, you can pass inputs:

```php
use Zenstruck\Console\CommandRunner;

/** @var \Symfony\Component\Console\Application $application */

CommandRunner::from($application, 'my:command')->run([
    'foo', // input 1
    '', // input 2 (<enter>)
    'y', // input 3
]);
```

By default, output is suppressed, you can optionally capture the output:

```php
use Zenstruck\Console\CommandRunner;

/** @var \Symfony\Component\Console\Application $application */

$output = new \Symfony\Component\Console\Output\BufferedOutput();

CommandRunner::from($application, 'my:command')
    ->withOutput($output) // any OutputInterface
    ->run()
;

$output->fetch(); // string (the output)
```

#### `RunsCommands`

You can give your [Invokable Commands](#invokablecommand) the ability to run other commands (defined
in the application) by using the `RunsCommands` trait. These _sub-commands_ will use the same
_output_ as the parent command.

```php
use Symfony\Component\Console\Command;
use Zenstruck\Console\InvokableCommand;
use Zenstruck\Console\RunsCommands;

class MyCommand extends InvokableCommand
{
    use RunsCommands;

    public function __invoke(): void
    {
        $this->runCommand('another:command'); // int (sub-command's run status)

        // pass arguments/options
        $this->runCommand('another:command arg --opt');

        // pass inputs for interactive commands
        $this->runCommand('another:command', [
            'foo', // input 1
            '', // input 2 (<enter>)
            'y', // input 3
        ])
    }
}
```

### `RunsProcesses`

You can give your [Invokable Commands](#invokablecommand) the ability to run other processes (`symfony/process` required)
by using the `RunsProcesses` trait. Standard output from the process is hidden by default but can be shown by
passing `-v` to the _parent command_. Error output is always shown. If the process fails, a `\RuntimeException`
is thrown.

```php
use Symfony\Component\Console\Command;
use Symfony\Component\Process\Process;
use Zenstruck\Console\InvokableCommand;
use Zenstruck\Console\RunsProcesses;

class MyCommand extends InvokableCommand
{
    use RunsProcesses;

    public function __invoke(): void
    {
        $this->runProcess('/some/script');

        // construct with array
        $this->runProcess(['/some/script', 'arg1', 'arg1']);

        // for full control, pass a Process itself
        $this->runProcess(
            Process::fromShellCommandline('/some/script')
                ->setTimeout(900)
                ->setWorkingDirectory('/')
        );
    }
}
```

### `CommandSummarySubscriber`

Add this event subscriber to your `Application`'s event dispatcher to display a summary after every command is run.
The summary includes the duration of the command and peak memory usage.

If using Symfony, configure it as a service to enable:

```yaml
# config/packages/zenstruck_console_extra.yaml

services:
    Zenstruck\Console\EventListener\CommandSummarySubscriber:
        autoconfigure: true
```

> [!NOTE]
> This will display a summary after every registered command runs.
