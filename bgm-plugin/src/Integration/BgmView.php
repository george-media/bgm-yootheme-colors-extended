<?php
/** Original BGM implementation. GPL-2.0-or-later. */
namespace Bgm\Plugin\System\BgmYoothemeColorsExtended\Integration;

use YOOtheme\View;

/** An unconditional invocation guard. All native rendering remains inherited. */
final class BgmView extends View
{
    private ?BgmRenderAdapter $bgmAdapter = null;

    public function bgmUseAdapter(BgmRenderAdapter $adapter): void
    {
        $this->bgmAdapter = $adapter;
    }

    public function render(string $name, array $parameters = []): string
    {
        if (!$this->bgmAdapter) {
            return parent::render($name, $parameters);
        }
        // Mirror only the documented parameter inheritance boundary. Parent owns its
        // evaluation stack, filters, _root flag, helpers and complete render algorithm.
        $inherited = end($this->parameters) ?: $this->getGlobals();
        return $this->bgmAdapter->guard($name, array_replace($inherited, $parameters),
            fn($name, $parameters) => parent::render($name, $parameters));
    }

    public function evaluate(string $template, array $parameters = []): string
    {
        $templates = $this->template;
        $inherited = $this->parameters;
        $bufferLevel = ob_get_level();
        try {
            if (!$this->bgmAdapter) {
                return parent::evaluate($template, $parameters);
            }
            // The native constructor binds this overridable evaluator as the innermost
            // loader, after FileLoader and every subsequently registered loader.
            return $this->bgmAdapter->load($template, $parameters,
                fn($template, $parameters) => parent::evaluate($template, $parameters));
        } catch (\Throwable $error) {
            // Native 5.0.52 evaluation does not unwind these frames/buffers on throw.
            // Restore the entry state so later sibling invocations cannot inherit it.
            $this->template = $templates;
            $this->parameters = $inherited;
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }
            throw $error;
        }
    }
}
