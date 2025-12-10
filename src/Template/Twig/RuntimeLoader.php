<?php

declare(strict_types= 1);

namespace Marshal\Application\Template\Twig;

use Mezzio\Helper\ServerUrlHelper;
use Mezzio\Helper\UrlHelperInterface;
use Psr\Container\ContainerInterface;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

class RuntimeLoader implements RuntimeLoaderInterface
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function load(string $class): ?object
    {
        switch ($class) {
            case UrlExtension::class:
                $serverUrlHelper = $this->container->get(ServerUrlHelper::class);
                if ($serverUrlHelper === null) {
                    throw new \RuntimeException(sprintf('Missing required `%s` dependency.', ServerUrlHelper::class));
                }

                $urlHelper = $this->container->get(UrlHelperInterface::class);
                if ($urlHelper === null) {
                    throw new \RuntimeException(sprintf('Missing required `%s` dependency.', UrlHelperInterface::class));
                }

                // @todo review $developmentServer
                $developmentServer = $this->container->get('config')['development']['server'] ?? null;
                $config = $this->container->has('config') ? $this->container->get('config') : [];
                $config = $config['resources']['assets_config'] ?? [];
                $assertsUrl = $config['assets_url'] ?? '/static/';
                $assetsVersion = $config['assets_version'] ?? '';
                return new $class($serverUrlHelper, $urlHelper, $assertsUrl, $assetsVersion, $developmentServer);

            default:
                return null;
        }
    }
}
