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

        // Soft delete visibility
        if (!empty($filters['only_trashed'])) {
            $query->onlyTrashed();
        } elseif (!empty($filters['with_trashed'])) {
            $query->withTrashed();
        }

        foreach ($filters as $field => $value) {
            if (in_array($field, ['q', 'page', 'per_page', 'limit', 'with_trashed', 'only_trashed'])) {
                continue;
            }

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
