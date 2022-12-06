<?php

/*
 * This file is part of the zenstruck/console-extra package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Console\Tests\Fixture;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Zenstruck\Console\Tests\Fixture\Command\ServiceCommand;
use Zenstruck\Console\Tests\Fixture\Command\ServiceSubscriberTraitCommand;
use Zenstruck\Console\Tests\Fixture\Command\WithAttributesServiceCommand;
use Zenstruck\Console\Tests\Fixture\Service\AnInterface;
use Zenstruck\Console\Tests\Fixture\Service\Implementation1;
use Zenstruck\Console\Tests\Fixture\Service\Implementation2;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader): void
    {
        $c->register(ServiceCommand::class)->setAutowired(true)->setAutoconfigured(true);
        $c->register(ServiceSubscriberTraitCommand::class)->setAutowired(true)->setAutoconfigured(true);

        if (self::VERSION_ID >= 60200) {
            $c->register(WithAttributesServiceCommand::class)->setAutowired(true)->setAutoconfigured(true);
        }

        $c->register('imp1', Implementation1::class);
        $c->register('imp2', Implementation2::class);
        $c->setAlias(AnInterface::class.' $imp1', 'imp1');
        $c->setAlias(AnInterface::class.' $imp2', 'imp2');

        $c->loadFromExtension('framework', [
            'secret' => 'S3CRET',
            'test' => true,
        ]);
    }
}
