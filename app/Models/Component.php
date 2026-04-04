<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Visus\Cuid2\Cuid2;

class Component extends Model
{
    /** @use HasFactory<\Database\Factories\ComponentFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'structure',
    ];

    protected $casts = [
        'structure' => 'array',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Component $component): void {
            if ($component->structure === null || $component->structure === []) {
                $component->structure = self::defaultStructure();
            }
        });
    }

    /**
     * Default component structure: a single root block with a CUID and no children.
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
