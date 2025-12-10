<?php

declare(strict_types=1);

namespace Marshal\Application\Template\Dom;

final class Layout
{
    public function __construct(private string $name, private array $config)
    {
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getMeta(): array
    {
        return $this->config['meta'] ?? [];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getScripts(): array
    {
        return $this->config['scripts'] ?? [];
    }

    public function getStyles(): array
    {
        return $this->config['styles'] ?? [];
    }
}
