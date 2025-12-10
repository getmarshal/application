<?php

declare(strict_types=1);

namespace Marshal\Application\Template\Twig;

use Marshal\Utils\FileSystem\Local\FileManager;
use Psr\Container\ContainerInterface;

final class TwigTemplateRendererFactory
{
    public function __invoke(ContainerInterface $container): TwigTemplateRenderer
    {
        $twigEnvironmentOptions = [
            'debug' => $container->get('config')['debug'] ?? FALSE,
            'use_yield' => TRUE,
        ];

        $config = $container->get('config')['twig'] ?? [];

        $templatesConfig = $container->get('config')['templates'] ?? [];

        $fileManager = $container->get(FileManager::class);
        \assert($fileManager instanceof FileManager);

        return new TwigTemplateRenderer($container, $fileManager, $config, $twigEnvironmentOptions, $templatesConfig);
    }
}
