<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Paragraph extends Component
{
    public function __construct(public array $block) {}

    public function render(): string
    {
        $output = '<p style="min-height: 1.5em;">';

        foreach ($this->block['children'] ?? [] as $child) {
            $type = $child['type'] ?? 'text';
            $componentName = 'App\\View\\Components\\'.ucfirst($type);
            if (class_exists($componentName)) {
                $component = new $componentName($child);
                $output .= $component->render();
            }
        }

        $output .= '</p>';

        return $output;
    }
}
