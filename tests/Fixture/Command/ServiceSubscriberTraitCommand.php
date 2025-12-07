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

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceMethodsSubscriberTrait;
use Zenstruck\Console\InvokableServiceCommand;
use Zenstruck\Console\IO;

#[AsCommand('service-subscriber-trait-command')]
final class ServiceSubscriberTraitCommand extends InvokableServiceCommand
{
    use ServiceMethodsSubscriberTrait;

    public function __invoke(IO $io, RouterInterface $router): void
    {
        $io->comment(\sprintf('IO: %s', \get_debug_type($io)));
        $io->comment(\sprintf('RouterInterface: %s', \get_debug_type($router)));
        $io->comment(\sprintf('LoggerInterface: %s', \get_debug_type($this->logger())));
    }

    #[SubscribedService]
    protected function logger(): LoggerInterface
    {
        return $this->container->get(__METHOD__);
    }
}
