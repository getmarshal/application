<?php

declare(strict_types=1);

namespace Marshal\Application\Template\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class TwigExtension extends AbstractExtension
{
    public function __construct(private array $config)
    {
    }

    public function getFunctions(): array
    {
        $functions = [];
        foreach ($this->config['functions'] ?? [] as $function) {
            $functions[] = new TwigFunction($function['name'], $function['callable'], $function['options'] ?? []);
        }
        return $functions;
    }

    public function getFilters(): array
    {
        $filters = [];
        foreach ($this->config['filters'] ?? [] as $function) {
            $filters[] = new TwigFilter($function['name'], $function['callable'], $function['options'] ?? []);
        }
        return $filters;
    }
}
