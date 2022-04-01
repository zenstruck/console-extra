<?php

namespace Zenstruck\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Terminal;
use Symfony\Component\Process\Process;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait RunsProcesses
{
    /**
     * @param string[]|string|Process $process
     */
    protected function runProcess($process): Process
    {
        if (!\class_exists(Process::class)) {
            throw new \LogicException('symfony/process required: composer require symfony/process');
        }

        if (!$this instanceof Command || !\method_exists($this, 'io')) {
            throw new \LogicException(\sprintf('"%s" can only be used with "%s" commands.', __TRAIT__, Invokable::class));
        }

        if (!$process instanceof Process) {
            $process = \is_string($process) ? Process::fromShellCommandline($process) : new Process($process);
        }

        $commandLine = $process->getCommandLine();
        $maxLength = \min((new Terminal())->getWidth(), IO::MAX_LINE_LENGTH) - 48;  // account for prefix/decoration length
        $last = null;

        if (\mb_strlen($commandLine) > $maxLength) {
            $commandLine = \sprintf('%s...%s',
                \mb_substr($commandLine, 0, (int) \ceil($maxLength / 2)),
                \mb_substr($commandLine, 0 - (int) \floor($maxLength / 2) - 3) // accommodate "..."
            );
        }

        $this->io()->comment(\sprintf('Running process: <comment>%s</comment>', $commandLine));

        $process->start();

        foreach ($process as $type => $buffer) {
            foreach (\array_filter(\explode("\n", $buffer)) as $line) {
                if (Process::ERR === $type || $this->io()->isVerbose()) {
                    $last = \sprintf('<%s>%s</%1$s> %s',
                        Process::ERR === $type ? 'error' : 'comment',
                        \mb_strtoupper($type),
                        $line
                    );

                    $this->io()->text($last);
                }
            }
        }

        if (!$process->isSuccessful()) {
            throw new \RuntimeException("Process failed: {$process->getExitCodeText()}.");
        }

        if ($last) {
            $this->io()->newLine();
        }

        return $process;
    }
}
