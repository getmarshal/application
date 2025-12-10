<?php

declare(strict_types=1);

namespace Marshal\Application\Template\Dom;

use Marshal\Application\Template\TemplateInterface;

final class DomTemplate implements TemplateInterface
{
    public const string TEMPLATE_FORMAT = "dom";

    public function __construct(private string $identifier, private array $config, private ?Layout $layout = null)
    {
    }

    public function getCollectionQuery(): array
    {
        return $this->config['collection'] ?? [];
    }

    public function getElements(): array
    {
        $elements = $this->config['elements'] ?? [];
        if (! \is_array($elements)) {
            return [];
        }

        $hydrated = [];
        foreach ($elements as $tag => $config) {
            $hydrated[] = new Element($tag, $config);
        }

        return $hydrated;
    }

    public function getFormat(): string
    {
        return self::TEMPLATE_FORMAT;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getLayout(): ?Layout
    {
        return $this->layout;
    }

    public function getMenus(): array
    {
        return $this->config['menus'] ?? [];
    }

    public function getQueryParams(): array
    {
        return $this->config['query_params'] ?? [];
    }

    public function hasCollectionQuery(): bool
    {
        return isset($this->config['collection']) && \is_array($this->config['collection']);
    }

    public function hasQueryParams(): bool
    {
        return isset($this->config['query_params']) && \is_array($this->config['query_params']);
    }

    public function hasLayout(): bool
    {
        return $this->layout !== null;
    }
}
