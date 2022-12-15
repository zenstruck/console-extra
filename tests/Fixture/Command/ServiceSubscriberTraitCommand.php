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
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceSubscriberTrait;
use Zenstruck\Console\InvokableServiceCommand;
use Zenstruck\Console\IO;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ServiceSubscriberTraitCommand extends InvokableServiceCommand
{
    use ServiceSubscriberTrait;

    public function __invoke(IO $io, RouterInterface $router): void
    {
        $io->comment(\sprintf('IO: %s', get_debug_type($io)));
        $io->comment(\sprintf('RouterInterface: %s', get_debug_type($router)));
        $io->comment(\sprintf('LoggerInterface: %s', get_debug_type($this->logger())));
    }

    public static function getDefaultName(): string
    {
        return 'service-subscriber-trait-command';
    }

    #[SubscribedService]
    private function logger(): LoggerInterface
    {
        return $this->container->get(__METHOD__);
    }
}
