<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Text extends Component
{
    /**
     * @param  array{type?: string, text?: string, marks?: list<string>, link?: array{url?: string, pageId?: int}}  $node
     */
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

        $rawUrl = $this->node['link']['url'] ?? null;
        $href = $this->safeHref($rawUrl);
        if ($href !== null) {
            $isSameSitePath = is_string($rawUrl) && str_starts_with(trim($rawUrl), '/');
            $attrs = $isSameSitePath
                ? 'href="'.$href.'"'
                : 'href="'.$href.'" target="_blank" rel="noopener noreferrer"';
            $output = '<a '.$attrs.'>'.$output.'</a>';
        }

        return $output;
    }

    private function safeHref(?string $raw): ?string
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $url = trim($raw);

        if (str_starts_with($url, '/')) {
            if (str_starts_with($url, '//')) {
                return null;
            }

            if (str_contains($url, ':')) {
                return null;
            }

            return htmlspecialchars($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        $parts = parse_url($url);
        if ($parts === false) {
            return null;
        }

        $scheme = strtolower($parts['scheme'] ?? '');
        if (! in_array($scheme, ['http', 'https', 'mailto'], true)) {
            return null;
        }

        return htmlspecialchars($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
