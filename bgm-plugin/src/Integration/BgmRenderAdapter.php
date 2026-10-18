<?php
/** Original BGM implementation. GPL-2.0-or-later. */
namespace Bgm\Plugin\System\BgmYoothemeColorsExtended\Integration;

use YOOtheme\View;
use YOOtheme\View\HtmlElement;
use YOOtheme\View\HtmlHelper;

/** Narrow, invocation-scoped adaptation at native structured attribute boundaries. */
final class BgmRenderAdapter
{
    private array $stack = [];
    private int $sequence = 0;
    private string $nonce;
    private string $root;

    private const PARENTS = ['grid_item' => 'grid', 'panel-slider_item' => 'panel-slider', 'popover_item' => 'popover', 'gallery_item' => 'gallery', 'description_list_item' => 'description_list', 'table_item' => 'table', 'map_item' => 'map'];
    private const TEMPLATES = ['template', 'template-content', 'template-title', 'template-link', 'template-media', 'template-image', 'template-marker'];
    private const TAGS = ['a', 'div', 'span', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hgroup', 'section', 'article', 'aside', 'header', 'footer', 'main', 'nav', 'address', 'figure', 'blockquote', 'ul', 'ol', 'li', 'dl', 'dt', 'dd', 'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'button'];

    public function __construct(string $elementsRoot)
    {
        $this->root = rtrim((string) realpath($elementsRoot), DIRECTORY_SEPARATOR);
        $this->nonce = bin2hex(random_bytes(8));
    }

    public function register(View $view): void
    {
        $html = $view['html'];
        if (!$view instanceof BgmView || !$html instanceof HtmlHelper || $this->root === '') {
            return;
        }
        $view->bgmUseAdapter($this);
        foreach (self::TAGS as $tag) {
            $callback = null;
            $callback = function (HtmlElement $element, array $params) use ($html, $tag, &$callback): ?string {
                return $this->transform($element, $params, $html, $callback, $tag);
            };
            $html->addTransform($tag, $callback);
            unset($callback);
        }
    }

    public function guard(string $name, array $parameters, callable $next): string
    {
        $parameters = $this->restore($parameters);
        // This outer frame is always inactive, including filtered short-circuit output
        // and postprocessing after a native template's active inner frame has returned.
        $this->stack[] = ['tokens' => [], 'values' => []];
        try {
            return $next($name, $parameters);
        } finally {
            array_pop($this->stack);
        }
    }

    public function load(string $name, array $parameters, callable $next): string
    {
        $context = ['tokens' => [], 'values' => []];
        $parameters = $this->restore($parameters);
        $file = realpath(str_ends_with($name, '.php') ? $name : "$name.php");
        if ($file && str_starts_with($file, $this->root . DIRECTORY_SEPARATOR)) {
            $relative = substr($file, strlen($this->root) + 1);
            if (preg_match('~^([^/]+)/templates/([^/]+)\.php$~D', $relative, $match)
                && in_array($match[2], self::TEMPLATES, true) && $this->knownTags($parameters)) {
                $type = $match[1];
                foreach (['props' => $type, 'element' => self::PARENTS[$type] ?? $type] as $key => $owner) {
                    if (!isset($parameters[$key]) || !is_array($parameters[$key])) {
                        continue;
                    }
                    foreach (BgmColourRegistry::FIELDS[$owner] ?? [] as $field => $kind) {
                        $value = $parameters[$key][$field] ?? null;
                        if (!is_string($value)) {
                            continue;
                        }
                        if (!($choice = BgmColourRegistry::choice($value, $kind))) {
                            continue;
                        }
                        [$shape, $component, $family] = $choice;
                        $sentinel = $shape . 'bgm-render-' . $this->nonce . '-' . ++$this->sequence;
                        $parameters[$key][$field] = $sentinel;
                        $context['values'][$sentinel] = ['value' => $value, 'field' => $field];
                        $token = 'uk-' . ($kind === 'panel' ? '' : "$component-") . $sentinel;
                        $replacement = "bgm-$component-$family";
                        // Native card hover is a behaviour trigger, not a custom colour identifier.
                        if ($component === 'card' && $this->hover($parameters, $key)) {
                            $replacement .= ' uk-card-hover';
                        }
                        $context['tokens'][$token] = $replacement;
                    }
                }
            }
        }
        $this->stack[] = $context;
        try {
            return $next($name, $parameters);
        } finally {
            array_pop($this->stack);
        }
    }

    private function restore(array $parameters): array
    {
        // View inherits parent parameters. Restore only our exact issued values in direct,
        // verified setting fields before deciding whether this invocation is in scope.
        $fields = array_unique(array_merge(...array_values(array_map('array_keys', BgmColourRegistry::FIELDS))));
        foreach (['props', 'element'] as $key) {
            foreach ($fields as $field) {
                if (isset($parameters[$key][$field]) && is_string($parameters[$key][$field])) {
                    $parameters[$key][$field] = $this->original($parameters[$key][$field], $field);
                }
            }
        }
        return $parameters;
    }

    private function knownTags(array $parameters): bool
    {
        foreach (['props', 'element'] as $key) {
            foreach (['html_element', 'item_element', 'title_element'] as $field) {
                $tag = $parameters[$key][$field] ?? '';
                if ($tag !== '' && (!is_string($tag) || !in_array($tag, self::TAGS, true))) {
                    return false;
                }
            }
        }
        return true;
    }

    private function hover(array $parameters, string $key): bool
    {
        $settings = $parameters[$key];
        return !empty($settings['panel_link_hover']) && !empty($settings['panel_link'])
            && !empty($parameters['props']['link']);
    }

    private function original(string $value, string $field): string
    {
        foreach (array_reverse($this->stack) as $context) {
            if (isset($context['values'][$value]) && $context['values'][$value]['field'] === $field) {
                return $context['values'][$value]['value'];
            }
        }
        return $value;
    }

    private function transform(HtmlElement $element, array $params, HtmlHelper $html, callable $self, string $tag): ?string
    {
        $context = end($this->stack);
        if (!$context || !$context['tokens'] || !isset($element->attrs['class'])) {
            return null;
        }
        $classes = $element->attrs['class'];
        $classes = is_array($classes) ? HtmlElement::expr($classes, $params) : $classes;
        if (!is_string($classes)) {
            return null;
        }
        $tokens = preg_split('/\s+/', trim($classes)) ?: [];
        if (!array_intersect($tokens, array_keys($context['tokens']))) {
            return null;
        }
        $copy = clone $element;
        $mapped = implode(' ', array_map(fn($token) => $context['tokens'][$token] ?? $token, $tokens));
        $copy->attrs['class'] = is_array($element->attrs['class']) ? [$mapped] : $mapped;
        // Native callbacks returning a rendered string short-circuit. Continue that same public
        // chain on the local copy, so later callbacks run once and may short-circuit normally.
        $after = false;
        foreach ($html->transforms[$tag] ?? [] as $callback) {
            if ($callback === $self) {
                $after = true;
                continue;
            }
            if ($after && ($result = $callback($copy, $params))) {
                return $result;
            }
        }
        return HtmlElement::tag($copy->name, $copy->attrs, $copy->contents, $params);
    }
}
