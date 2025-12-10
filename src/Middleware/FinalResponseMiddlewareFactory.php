<?php

declare(strict_types=1);

namespace Marshal\Application\Middleware;

use Mezzio\Helper\UrlHelperInterface;
use Psr\Container\ContainerInterface;

final class FinalResponseMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): FinalResponseMiddleware
    {
        $urlHelper = $container->get(UrlHelperInterface::class);
        return new FinalResponseMiddleware($urlHelper);
    }
}
