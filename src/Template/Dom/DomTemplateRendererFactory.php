<?php

declare(strict_types= 1);

namespace Marshal\Application\Template\Dom;

use Laminas\Escaper\Escaper;
use Psr\Container\ContainerInterface;

final class DomTemplateRendererFactory
{
    public function __invoke(ContainerInterface $container): DomTemplateRenderer
    {
        $escaper = new Escaper;
        $menuConfigs = $container->get('config')['menus'] ?? [];
        $layoutConfigs = $container->get('config')['layouts'] ?? [];
        return new DomTemplateRenderer($escaper, $menuConfigs, $layoutConfigs);
    }
}
