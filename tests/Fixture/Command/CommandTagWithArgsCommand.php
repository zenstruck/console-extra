<?php

namespace Zenstruck\RadCommand\Tests\Fixture\Command;

use Zenstruck\RadCommand;

/**
 * @command some:command arg1 ?arg2 arg3=default arg4="default with space" ?arg5[] --option1 --option2= --option3=default --option4="default with space" --o|option5[]
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CommandTagWithArgsCommand extends RadCommand
{
}
