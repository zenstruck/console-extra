<?php

/*
 * This file is part of the zenstruck/console-extra package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Console;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Zenstruck\Console\Attribute\Argument;
use Zenstruck\Console\Attribute\Option;

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
                    static function(\ReflectionParameter $parameter) {
                        if (!$type = $parameter->getType()) {
                            return null;
                        }

                        if (!$type instanceof \ReflectionNamedType) {
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

                        if ($parameter->getAttributes(Option::class) || $parameter->getAttributes(Argument::class)) {
                            return null; // don't auto-inject options/arguments
                        }

                        $attributes = \array_map(static fn(\ReflectionAttribute $a) => $a->newInstance(), $parameter->getAttributes());

                        if (!$attributes && $type->isBuiltin()) {
                            return null; // an attribute (ie Autowire) is required for built-in types
                        }

                        return new SubscribedService('invoke:'.$parameter->name, $name, $type->allowsNull(), $attributes); // @phpstan-ignore-line
                    },
                    self::invokeParameters(),
                ),
            ),
        );

        return [...$services, ParameterBagInterface::class];
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach (self::getSubscribedServices() as $serviceId) {
            [$serviceId, $optional] = self::parseServiceId($serviceId);

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

    #[Required]
    public function setInvokeContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    final protected function parameter(string $name): mixed
    {
        return $this->container()->get(ParameterBagInterface::class)->get($name);
    }

    /**
     * @return array{0:string,1:bool}
     */
    private static function parseServiceId(string|SubscribedService $service): array
    {
        if ($service instanceof SubscribedService) {
            return [(string) $service->key, $service->nullable];
        }

        return [
            \ltrim($service, '?'),
            \str_starts_with($service, '?'),
        ];
    }

    private function container(): ContainerInterface
    {
        if (!isset($this->container)) {
            throw new \LogicException(\sprintf('Container not available in "%s", is this class auto-configured?', static::class));
        }

        return $this->container;
    }
}
