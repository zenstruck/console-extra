<?php

/*
 * This file is part of the zenstruck/console-extra package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Console\Tests\Fixture\Command;

use Zenstruck\Console\RunsProcesses;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class RunsProcessesCommand extends InvokableCommand
{
    use RunsProcesses;

    public function __invoke(): void
    {
        if ($this->io()->option('fail')) {
            $this->runProcess('invalid');
        }

        $sensitive = $this->io()->option('sensitive') ? ['composer', 'ls'] : [];

        $this->runProcess('ls', $sensitive);
    }

    protected function configure(): void
    {
        $this
            ->setName('process')
            ->addOption('fail')
            ->addOption('sensitive')
        ;
    }
}
