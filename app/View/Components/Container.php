<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Container extends Component
{
    public function __construct(public array $block) {}

    public function render(): string
    {
        $config = $this->block['config'] ?? [];
        $direction = $config['direction'] ?? 'column';
        $gap = (int) ($config['gap'] ?? 0);
        $padding = (int) ($config['padding'] ?? 20);

        $style = sprintf(
            'display: flex; flex-direction: %s; gap: %dpx; padding: %dpx; min-height: 6.25rem; width: 100%%;',
            $direction === 'row' ? 'row' : 'column',
            $gap,
            $padding
        );

        $output = '<div style="'.e($style).'">';

        foreach ($this->block['children'] ?? [] as $child) {
            $componentName = 'App\\View\\Components\\'.ucfirst($child['type'] ?? '');
            if (class_exists($componentName)) {
                $component = new $componentName($child);
                $output .= $component->render();
            }
        }

        $output .= '</div>';

        return $output;
    }
}
