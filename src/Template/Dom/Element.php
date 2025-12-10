<?php

declare(strict_types=1);

namespace Marshal\Application\Template\Dom;

final class Element
{
    public function __construct(private string $tag, private array $config)
    {
    }

    public function getAttributes(): array
    {
        return $this->config['attributes'] ?? [];
    }

    public function getDataValue(): array|string
    {
        return $this->config['data'];
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * @return array<Element>
     */
    public function getChildren(): array
    {
        $children = [];
        foreach ($this->config['children'] ?? [] as $tag => $config) {
            $children[] = new Element($tag, $config);
        }

        return $children;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getTextContent(): string
    {
        return $this->config['textContent'];
    }

    public function hasChildren(): bool
    {
        return ! empty($this->getChildren());
    }

    public function hasDataValue(): bool
    {
        return isset($this->config['data']);
    }

    public function hasTextContent(): bool
    {
        return isset($this->config['textContent']);
    }

    public function isCollection(): bool
    {
        return isset($this->config['type']) && $this->config['type'] === "collection";
    }
}
