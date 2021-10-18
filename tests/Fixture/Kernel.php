<?php

namespace Zenstruck\RadCommand\Tests\Fixture;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Zenstruck\RadCommand\Tests\Fixture\Command\ServiceCommand;
use Zenstruck\RadCommand\Tests\Fixture\Command\ServiceSubscriberTraitCommand;

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

        $c->loadFromExtension('framework', [
            'secret' => 'S3CRET',
            'test' => true,
        ]);
    }

    protected function configureRoutes($routes): void
    {
        // TODO: Implement configureRoutes() method.
    }
}
