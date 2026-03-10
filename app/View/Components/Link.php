<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Link extends Component
{
    public function __construct(public array $node) {}

    public function render()
    {
        $href = e($this->node['attrs']['href'] ?? '');
        $target = e($this->node['attrs']['target'] ?? '_self');

        $output = "<a href=\"{$href}\" target=\"{$target}\">";

        foreach ($this->node['children'] ?? [] as $child) {
            $componentName = 'App\\View\\Components\\'.ucfirst($child['type']);
            if (class_exists($componentName)) {
                $component = new $componentName($child);
                $output .= $component->render();
            }
        }

        $output .= '</a>';

        return $output;
    }
}
