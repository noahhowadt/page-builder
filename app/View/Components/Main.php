<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Main extends Component
{
    public function __construct(public array $block) {}

    public function render()
    {
        $output = '<main>';

        foreach ($this->block['children'] ?? [] as $child) {
            $componentName = 'App\\View\\Components\\'.ucfirst($child['type']);
            if (class_exists($componentName)) {
                $component = new $componentName($child);
                $output .= $component->render();
            }
        }

        $output .= '</main>';

        return $output;
    }
}
