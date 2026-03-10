<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Visus\Cuid2\Cuid2;

class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'is_published',
        'published_at',
        'structure',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'structure' => 'array',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Page $page): void {
            if ($page->structure === null || $page->structure === []) {
                $page->structure = self::defaultStructure();
            }
        });
    }

    /**
     * Default page structure: a single root block with a CUID and no children.
     *
     * @return array{id: string, type: string, children: array<int, mixed>}
     */
    public static function defaultStructure(): array
    {
        return [
            'id' => Cuid2::generate()->toString(),
            'type' => 'root',
            'children' => [],
        ];
    }
}
