<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Text extends Component
{
    public function __construct(public array $node) {}

    public function render(): string
    {
        $text = e($this->node['text'] ?? '');
        $marks = $this->node['marks'] ?? [];

        $output = '';

        foreach ($marks as $mark) {
            $output .= match ($mark) {
                'strong', 'bold' => '<strong>',
                'em', 'italic' => '<em>',
                'code' => '<code>',
                'underline' => '<u>',
                default => '',
            };
        }

        $output .= $text;

        foreach (array_reverse($marks) as $mark) {
            $output .= match ($mark) {
                'strong', 'bold' => '</strong>',
                'em', 'italic' => '</em>',
                'code' => '</code>',
                'underline' => '</u>',
                default => '',
            };
        }

        return $output;
    }
}
