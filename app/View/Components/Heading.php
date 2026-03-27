<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Heading extends Component
{
    /** @var array{level?: 1|2|3|4|5|6} */
    private const LEVEL_STYLES = [
        1 => 'font-size: 2.25rem; font-weight: bold;',
        2 => 'font-size: 1.5rem; font-weight: bold;',
        3 => 'font-size: 1.25rem; font-weight: bold;',
        4 => 'font-size: 1.125rem; font-weight: bold;',
        5 => 'font-size: 1rem; font-weight: bold;',
        6 => 'font-size: 0.875rem; font-weight: bold;',
    ];

    public function __construct(public array $block) {}

    public function render(): string
    {
        $level = (int) ($this->block['config']['level'] ?? 1);
        $level = $level >= 1 && $level <= 6 ? $level : 1;
        $tag = 'h'.$level;
        $style = self::LEVEL_STYLES[$level] ?? self::LEVEL_STYLES[1];

        $output = "<{$tag} style=\"".e($style).'">';

        foreach (($this->block['content'] ?? $this->block['children'] ?? []) as $child) {
            $type = $child['type'] ?? 'text';
            $componentName = 'App\\View\\Components\\'.ucfirst($type);
            if (class_exists($componentName)) {
                $component = new $componentName($child);
                $output .= $component->render();
            }
        }

        $output .= "</{$tag}>";

        return $output;
    }
}
