<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UniqueSlug
{
    public static function forModel(Model $model, string $source, string $column = 'slug'): string
    {
        $base = Str::slug($source);
        $slug = $base;
        $counter = 1;

        while (static::exists($model, $column, $slug)) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    protected static function exists(Model $model, string $column, string $slug): bool
    {
        return $model->newQuery()->where($column, $slug)->exists();
    }
}
