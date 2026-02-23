<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Searchable
{
    /**
     * Scope a query to dynamically filter based on request parameters
     *
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    public function scopeSearch(Builder $query, array $filters)
    {
        $fillable = $this->getFillable();
        $primaryKey = $this->getKeyName();

        foreach ($filters as $field => $value) {
            // Ignore pagination or specific global search params
            if (in_array($field, ['q', 'page', 'per_page'])) {
                continue;
            }

            // Only allow filtering by fillable fields or primary key
            if (in_array($field, $fillable) || $field === $primaryKey) {
                if (is_string($value) && !\Str::isUuid($value) && !is_numeric($value) && !strtotime($value)) {
                    $query->where($field, 'ILIKE', "%{$value}%");
                } else {
                    $query->where($field, $value);
                }
            }
        }

        return $query;
    }
}
