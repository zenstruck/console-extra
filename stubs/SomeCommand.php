<?php

use Symfony\Component\Console\Command\Command;
use Zenstruck\Console\RunsCommands;
use Zenstruck\Console\RunsProcesses;

class SomeCommand extends Command
{
    use RunsCommands, RunsProcesses;
}
