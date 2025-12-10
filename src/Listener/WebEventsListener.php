<?php

declare(strict_types=1);

namespace Marshal\Application\Listener;

use loophp\collection\Collection;
use Marshal\Application\Template\TemplateManager;
use Marshal\ContentManager\Schema\Content;
use Marshal\Platform\Web\Event\RenderTemplateEvent;
use Marshal\Application\Template\Dom\DomTemplate;
use Marshal\Application\Template\Dom\DomTemplateRenderer;
use Marshal\Application\Template\TemplateRendererInterface;
use Marshal\Application\Template\Twig\TwigTemplate;
use Marshal\Application\Template\Twig\TwigTemplateRenderer;
use Psr\Container\ContainerInterface;

class WebEventsListener
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function onRenderTemplateEvent(RenderTemplateEvent $event): void
    {
        $templateManager = $this->container->get(TemplateManager::class);
        \assert($templateManager instanceof TemplateManager);

        try {
            $template = $templateManager->get($event->getTemplate());
        } catch (\Throwable $e) {
            return;
        }

        // get the template renderer
        $renderer = match ($template->getFormat()) {
            DomTemplate::TEMPLATE_FORMAT        => $this->container->get(DomTemplateRenderer::class),
            TwigTemplate::TEMPLATE_FORMAT       => $this->container->get(TwigTemplateRenderer::class),
            default                             => null
        };

        if (! $renderer instanceof TemplateRendererInterface) {
            return;
        }

        // prepare template data
        $data = [];
        foreach ($event->getData() as $key => $value) {
            if (\is_array($value)) {
                $data[$key] = $value;
            }

            if ($value instanceof Content) {
                $data[$key] = $value->toArray();
            }

            if ($value instanceof Collection) {
                $collection = [];
                foreach ($value as $row) {
                    if (\is_array($row)) {
                        $collection[] = $row;
                    }

                    if ($row instanceof Content) {
                        $collection[] = $row->toArray();
                    }
                }
                $data[$key] = $collection;
            }

            if (\is_scalar($value)) {
                $data[$key] = $value;
            }
        }

        // render the template
        $html = $renderer->render($template, $data);
        $event->setContents($html);

        // add the main menu for full pages
        $menuConfigs = $this->container->get('config')['menus'] ?? [];
        foreach ($template->getMenus() as $menu) {
            if (! isset($menuConfigs[$menu])) {
                continue;
            }

            $this->buildMenu($menuConfigs[$menu]);
        }

        try {
            $dom = \Dom\HTMLDocument::createFromString($html, \LIBXML_HTML_NOIMPLIED);
        } catch (\Throwable) {
            return;
        }

        if ($dom->head instanceof \Dom\HTMLElement) {
            // append a generator element
            $meta = $dom->createElement('meta');
            $meta->setAttribute('name', 'generator');
            $meta->setAttribute('value', 'marshal');
            $dom->head->appendChild($meta);

            // append main menu
            // @todo menu system
            $nav = $dom->createElement('nav');
            $nav->setAttribute('id', 'menu');
            $nav->setAttribute('class', 'container-fluid d-flex align-items-center position-sticky py-2 bg-black bg-opacity-75');
            $nav->setAttribute('style', 'top: 0;');

            $nav->appendChild($this->getNavigationHead($dom));
            $nav->appendChild($this->getDynamicNavigation($dom));
            $nav->appendChild($this->getNavigationTail($dom));

            $dom->body->prepend($nav);
        }

        // update the contents
        $event->setContents($dom->saveHtml());
    }

    private function buildMenu(array $config): void
    {
        $this->container->get('config')['menus'] ?? [];
    }

    private function getNavigationHead(\Dom\HTMLDocument $dom): \Dom\HTMLElement
    {
        $el = $dom->createElement('div');
        $el->setAttribute('class', 'd-flex align-items-center');

        $home = $dom->createElement('a');
        $home->setAttribute('href', '/');
        $home->textContent = 'Home';

        $search = $dom->createElement('form');
        $search->setAttribute('action', '');
        $search->setAttribute('method', 'GET');
        $input = $dom->createElement('input');
        $input->setAttribute('name', 'q');
        $input->setAttribute('type', 'search');
        $button = $dom->createElement('button');
        $button->setAttribute('type', 'submit');
        $button->setAttribute('class', 'btn');
        $button->textContent = 'Search';
        $search->append($input, $button);

        $el->append($home, $search);
        return $el;
    }

    private function getDynamicNavigation(\Dom\HTMLDocument $dom): \Dom\HTMLElement
    {
        $el = $dom->createElement('div');
        $el->setAttribute('id', 'dynamic-nav');
        return $el;
    }

    private function getNavigationTail(\Dom\HTMLDocument $dom): \Dom\HTMLElement
    {
        $el = $dom->createElement('div');
        $el->setAttribute('class', 'ms-auto d-flex align-items-center');

        $notifications = $dom->createElement('span');
        $notifications->textContent = 'Notifications';

        $settings = $dom->createElement('span');
        $settings->textContent = 'Settings';

        $el->append($notifications, $settings);
        return $el;
    }
}
