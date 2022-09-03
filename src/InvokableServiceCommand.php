<?php

namespace Zenstruck\Console;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * All the benefits of {@see Invokable} but also allows for auto-injection of
 * any service from your Symfony DI container. You can think of it as
 * "Invokable Service Controllers" (with 'controller.service_arguments') but
 * for commands. Instead of a "Request", you inject {@see IO}.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class InvokableServiceCommand extends Command implements ServiceSubscriberInterface
{
    use Invokable { execute as private invokableExecute; }

    private ContainerInterface $container;

    public static function getSubscribedServices(): array
    {
        $services = \array_values(
            \array_filter(
                \array_map(
                    static function(\ReflectionParameter $parameter): ?string {
                        if (!$type = $parameter->getType()) {
                            return null;
                        }

                        if (!$type instanceof \ReflectionNamedType || $type->isBuiltin()) {
                            return null;
                        }

                        $name = $type->getName();

                        if (\is_a($name, InputInterface::class, true)) {
                            return null;
                        }

                        if (\is_a($name, OutputInterface::class, true)) {
                            return null;
                        }

                        if (\is_a($name, StyleInterface::class, true)) {
                            return null;
                        }

                        return $type->allowsNull() ? '?'.$name : $name;
                    },
                    self::invokeParameters()
                )
            )
        );

        return [...$services, ParameterBagInterface::class];
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach (self::getSubscribedServices() as $serviceId) {
            $optional = 0 === \mb_strpos($serviceId, '?');
            $serviceId = \ltrim($serviceId, '?');

            try {
                $value = $this->container()->get($serviceId);
            } catch (NotFoundExceptionInterface $e) {
                if (!$optional) {
                    // not optional
                    throw $e;
                }

                // optional
                $value = null;
            }

            $this->addArgumentFactory($serviceId, static fn() => $value);
        }

        return $this->invokableExecute($input, $output);
    }

    /**
     * @required
     */
    #[Required]
    public function setInvokeContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    final protected function parameter(string $name): mixed
    {
        return $this->container()->get(ParameterBagInterface::class)->get($name);
    }

    private function container(): ContainerInterface
    {
        if (!isset($this->container)) {
            throw new \LogicException(\sprintf('Container not available in "%s", is this class auto-configured?', static::class));
        }

        return $this->container;
    }
}
