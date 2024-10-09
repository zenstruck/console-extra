<?php

namespace Zenstruck\Console\Tests\Fixture\Command;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceMethodsSubscriberTrait;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

if (trait_exists(ServiceMethodsSubscriberTrait::class)) {
    final class ServiceSubscriberTraitCommand extends BaseServiceSubscriberTraitCommand
    {
        use ServiceMethodsSubscriberTrait;

        #[SubscribedService]
        protected function logger(): LoggerInterface
        {
            return $this->container->get(__METHOD__);
        }
    }
} else {
    final class ServiceSubscriberTraitCommand extends BaseServiceSubscriberTraitCommand
    {
        use ServiceSubscriberTrait;

        #[SubscribedService]
        protected function logger(): LoggerInterface
        {
            return $this->container->get(__METHOD__);
        }
    }
}
