<?php

declare(strict_types=1);

namespace Marshal\Application\Template\Twig;

use Marshal\Application\Template\TemplateInterface;
use Marshal\Application\Template\TemplateRendererInterface;
use Marshal\Utils\FileSystem\Local\FileManager;
use Psr\Container\ContainerInterface;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\ArrayLoader;

class TwigTemplateRenderer implements TemplateRendererInterface
{
    public function __construct(
        private ContainerInterface $container,
        private FileManager $fileManager,
        private array $config = [],
        private array $options = [],
        private array $templatesConfig = []
    ) {
    }

    public function render(TemplateInterface $template, array $data): string
    {
        if (! $template instanceof TwigTemplate) {
            throw new \InvalidArgumentException(\sprintf(
                "Expected %s, given %s instead",
                TwigTemplate::class,
                \get_debug_type($template)
            ));
        }

        $loaderData = ['__template' => $this->getTemplateContents($template->getIdentifier())];
        foreach ($this->resolveTemplateIncludes($template->getIdentifier()) as $k) {
            $loaderData[$k] = $this->getTemplateContents($k);
        }

        $loader = new ArrayLoader($loaderData);
        $twig = new Environment($loader, $this->options);
        $twig->addExtension(new TwigExtension($this->config));
        if (isset($this->options['debug']) && TRUE === $this->options['debug']) {
            $twig->addExtension(new DebugExtension());
        }

        foreach ($this->config['runtime_loaders'] as $loader) {
            $twig->addRuntimeLoader($this->container->get($loader));
        }

        return $twig->render('__template', $data);
    }

    private function getTemplateContents(string $templateIdentifier): string
    {
        return $this->fileManager->getTemplateContents($this->templatesConfig[$templateIdentifier]['filename']);
    }

    private function resolveTemplateIncludes(string $templateName): array
    {
        $includes = [];
        foreach ($this->templatesConfig[$templateName]['includes'] ?? [] as $include) {
            if (! isset($this->templatesConfig[$include])) {
                continue;
            }

            if (! isset($this->templatesConfig[$include]['filename'])) {
                continue;
            }

            $includes[] = $include;
        }

        return $includes;
    }
}
