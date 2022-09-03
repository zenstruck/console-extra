<?php

namespace Zenstruck\Console\Tests\Fixture;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Zenstruck\Console\Tests\Fixture\Command\ServiceCommand;
use Zenstruck\Console\Tests\Fixture\Command\ServiceSubscriberTraitCommand;

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

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->register(ServiceCommand::class)->setAutowired(true)->setAutoconfigured(true);
        $container->register(ServiceSubscriberTraitCommand::class)->setAutowired(true)->setAutoconfigured(true);

        $container->loadFromExtension('framework', [
            'secret' => 'S3CRET',
            'http_method_override' => false,
            'test' => true,
        ]);
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        // TODO: Implement configureRoutes() method.
    }
}
