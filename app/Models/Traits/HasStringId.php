<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;

trait HasStringId
{
    /**
     * Boot the trait to set key type and auto-generate ID.
     */
    protected static function bootHasStringId(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                // Generate a unique 25-character CUID-compatible alphanumeric string
                $model->{$model->getKeyName()} = 'c' . strtolower(Str::random(24));
            }
        });
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * Get the auto-incrementing key type.
     */
    public function getKeyType(): string
    {
        return 'string';
    }
}
