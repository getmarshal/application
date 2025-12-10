<?php

declare(strict_types= 1);

namespace Marshal\Application\Template\Dom;

use Laminas\Escaper\Escaper;
use Marshal\Application\Template\TemplateInterface;
use Marshal\Application\Template\TemplateRendererInterface;

final class DomTemplateRenderer implements TemplateRendererInterface
{
    public function __construct(private Escaper $escaper, private array $menuConfigs, private array $layoutConfigs)
    {
    }

    public function render(TemplateInterface $template, array $data): string
    {
        if (! $template instanceof DomTemplate) {
            throw new \InvalidArgumentException(\sprintf(
                "Expected %s, given %s instead",
                DomTemplate::class,
                \get_debug_type($template)
            ));
        }

        if (! $template->hasLayout()) {
            $dom = \Dom\HTMLDocument::createEmpty();
        } else {
            $dom = \Dom\HTMLDocument::createFromString('<!DOCTYPE html>');
            foreach ($template->getLayout()->getMeta() as $name => $value) {
                $meta = $dom->createElement('meta');
                $meta->setAttribute($name, $this->escaper->escapeHtmlAttr($value));
                $dom->head->appendChild($meta);
            }

            foreach ($template->getLayout()->getStyles() as $style) {
                $link = $dom->createElement('link');
                $link->setAttribute('href', $this->escaper->escapeHtmlAttr($style));
                $link->setAttribute('rel', 'stylesheet');
                $dom->head->appendChild($link);
            }

            foreach ($template->getLayout()->getScripts() as $src) {
                $script = $dom->createElement('script');
                $script->setAttribute('src', $this->escaper->escapeHtmlAttr($src));
                $script->setAttribute('type', 'text/javascript');
                $dom->head->appendChild($script);
            }
        }

        foreach ($template->getMenus() as $menu) {
            if (! isset($this->menuConfigs[$menu])) {
                continue;
            }

            $this->buildMenu($dom, $this->menuConfigs[$menu]);
        }

        foreach ($template->getElements() as $element) {
            \assert($element instanceof Element);

            $node = $dom->createElement($element->getTag());
            $this->applyElementAttributes($element, $node);
            $this->buildNode($element, $dom, $node, $data);

            $dom->body instanceof \Dom\HTMLElement
                ? $dom->body->appendChild($node)
                : $dom->appendChild($node);
        }

        return $dom->saveHtml();
    }

    private function applyElementAttributes(Element $element, \Dom\HTMLElement $node): void
    {
        foreach ($element->getAttributes() as $name => $value) {
            $node->setAttribute($name, $this->escaper->escapeHtmlAttr($value));
        }
    }

    private function buildMenu(\Dom\HTMLDocument $doc, array $config): void
    {
        $nav = $doc->createElement('nav');
        if ($doc->body instanceof \Dom\HTMLElement) {
            $doc->appendChild($nav);
        }
    }

    private function buildNode(Element $element, \Dom\HTMLDocument $dom, \Dom\HTMLElement $node, array $data): void
    {
        foreach ($element->getChildren() as $childElement) {
            $el = $dom->createElement($childElement->getTag());
            $this->applyElementAttributes($childElement, $el);

            // recursively build child elements
            if ($childElement->hasChildren() && ! $childElement->isCollection()) {
                $this->buildNode($childElement, $dom, $el, $data);
            }

            if ($childElement->hasTextContent()) {
                $this->setTextContent($el, $childElement->getTextContent());
            }

            if ($childElement->hasDataValue() && ! $childElement->isCollection()) {
                $dataValue = $childElement->getDataValue();
                if (\is_string($dataValue) && isset($data[$dataValue])) {
                    $this->setTextContent($el, $data[$dataValue]);

                } elseif (\is_array($dataValue)) {
                    foreach ($dataValue as $key => $value) {
                        if (! isset($data[$key])) {
                            continue;
                        }

                        if (\is_string($value) && \is_array($data[$key]) && \is_string($data[$key][$value])) {
                            $this->setTextContent($el, $data[$key][$value]);
                        }
                    }
                }
            }

            if ($childElement->isCollection() && $childElement->hasDataValue()) {
                $dataValue = $childElement->getDataValue();
                if (\is_string($dataValue) && isset($data[$dataValue]) && \is_iterable($data[$dataValue])) {
                    foreach ($childElement->getChildren() as $grandChild) {
                        foreach ($data[$dataValue] as $row) {
                            if (! $grandChild->hasDataValue()) {
                                continue;
                            }
                            var_dump(11113);

                            $grandChildEl = $dom->createElement($grandChild->getTag());
                            $this->applyElementAttributes($grandChild, $grandChildEl);

                            $grandChildDataValue = $grandChild->getDataValue();
                            if (\is_string($grandChildDataValue) && $key === $grandChildDataValue && \is_scalar($value)) {
                                $grandChildEl->textContent = $value;
                            }
                            $el->appendChild($grandChildEl);
                        }
                    }
                }
            }

            $node->appendChild($el);
        }
    }

    private function setTextContent(\Dom\HTMLElement $element, string $textContent): void
    {
        $element->textContent = $this->escaper->escapeHtml($textContent);
    }
}
