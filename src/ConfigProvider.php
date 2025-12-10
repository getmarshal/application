<?php

declare(strict_types=1);

namespace Marshal\Application;

final class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            "commands"              => $this->getCommands(),
            "dependencies"          => $this->getDependencies(),
            "events"                => $this->getEventsConfig(),
            "layouts"               => $this->getLayoutsConfig(),
            "middleware_pipeline"   => $this->getMiddlewarePipeline(),
            // "navigation"            => $this->getNavigationConfig(),
            "templates"             => $this->getTemplates(),
            "twig"                  => $this->getTwigConfig(),
        ];
    }

    private function getCommands(): array
    {
        return [
            Command\FetchContentCommand::NAME => Command\FetchContentCommand::class,
        ];
    }

    private function getDependencies(): array
    {
        return [
            "delegators" => [
                Command\FetchContentCommand::class => [
                    \Marshal\EventManager\EventDispatcherDelegatorFactory::class,
                ],
                Handler\ContentPageHandler::class => [
                    \Marshal\EventManager\EventDispatcherDelegatorFactory::class,
                ],
                Middleware\ReadCollectionMiddleware::class => [
                    \Marshal\EventManager\EventDispatcherDelegatorFactory::class,
                ],
                Middleware\ReadContentMiddleware::class => [
                    \Marshal\EventManager\EventDispatcherDelegatorFactory::class,
                ],
            ],
            "factories" => [
                AppManager::class                                           => AppManagerFactory::class,
                Command\FetchContentCommand::class                          => \Laminas\ServiceManager\Factory\InvokableFactory::class,
                Handler\ContentPageHandler::class                           => Handler\ContentPageHandlerFactory::class,
                Middleware\FinalResponseMiddleware::class                   => Middleware\FinalResponseMiddlewareFactory::class,
                Middleware\ReadCollectionMiddleware::class                  => \Laminas\ServiceManager\Factory\InvokableFactory::class,
                Middleware\ReadContentMiddleware::class                     => \Laminas\ServiceManager\Factory\InvokableFactory::class,
                Listener\WebEventsListener::class                           => Listener\WebEventsListenerFactory::class,
                Template\Dom\DomTemplateRenderer::class                     => Template\Dom\DomTemplateRendererFactory::class,
                Template\TemplateManager::class                             => Template\TemplateManagerFactory::class,
                Template\Twig\RuntimeLoader::class                          => Template\Twig\RuntimeLoaderFactory::class,
                Template\Twig\TwigTemplateRenderer::class                   => Template\Twig\TwigTemplateRendererFactory::class,
            ],
        ];
    }

    private function getEventsConfig(): array
    {
        return [
            'listeners' => [
                Listener\WebEventsListener::class => [
                    \Marshal\Platform\Web\Event\RenderTemplateEvent::class => [
                        'listener' => 'onRenderTemplateEvent',
                    ],
                ],
            ],
        ];
    }

    private function getLayoutsConfig(): array
    {
        return [
            "main::layout" => [
                "meta" => [
                    "charset" => "utf-8",
                    "viewport" => "width=device-width;initial-scale=1",
                ],
                "scripts" => [
                    "/static/js/htmx.min.js",
                    "/static/js/idiomorph.min.js",
                    "/static/js/idiomorph-ext.min.js",
                    "/static/js/bootstrap.bundle.min.js",
                ],
                "styles" => [
                    "/static/css/bootstrap.css",
                    "/static/css/styles.css",
                    "/static/css/football.css",
                ],
            ],
        ];
    }

    private function getMiddlewarePipeline(): array
    {
        return [
            \Marshal\Platform\PlatformMiddleware::class,
            \Mezzio\Router\Middleware\RouteMiddleware::class,
            \Mezzio\Router\Middleware\MethodNotAllowedMiddleware::class,
            \Mezzio\Router\Middleware\DispatchMiddleware::class,
            Middleware\FinalResponseMiddleware::class,
        ];
    }

    private function getNavigationConfig(): array
    {
        return [
            "paths" => [
                "/content/{app}[/{schema}]" => [
                    "methods" => ["GET", "POST"],
                    "middleware" => [
                        Handler\ContentPageHandler::class,
                    ],
                    "name" => "marshal::content-page",
                ],
            ],
        ];
    }

    private function getTemplates(): array
    {
        return [
            "marshal::error-404" => [
                "filename" => __DIR__ . "/../template/error-404.twig",
            ],
            "main::layout" => [
                "filename" => __DIR__ . "/../template/layout.twig",
            ],
        ];
    }

    private function getTwigConfig(): array
    {
        return [
            "runtime_loaders" => [
                Template\Twig\RuntimeLoader::class,
            ],
            "functions" => [
                [
                    "name" => "media",
                    "callable" => [Template\Twig\UrlExtension::class, "media"],
                    "options" => [
                        "needs_context" => true,
                    ],
                ],
                [
                    "name" => "path",
                    "callable" => [Template\Twig\UrlExtension::class, "path"],
                ],
                [
                    "name" => "static",
                    "callable" => [Template\Twig\UrlExtension::class, "static"],
                    "options" => [
                        "needs_context" => true,
                        "needs_environment" => true,
                    ],
                ],
            ],
        ];
    }
}
