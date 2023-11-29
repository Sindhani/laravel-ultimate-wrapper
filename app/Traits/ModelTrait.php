<?php

namespace App\Traits;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait ModelTrait
{
    public function persist($data, $save = true): static
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }

        if ($save) {
            $this->save();
        }

        return $this;
    }

    /**
     * @param Builder $query
     * @param $s
     *
     * @return Builder
     */
    public function scopeSearch(Builder $query, $s): Builder
    {
        if (! $s || $s == '') {
            return $query;
        }
        if ($this->search_fields) {
            $column = $this->search_fields;
        } else {
            $column = Schema::getColumnListing($this->getTable());
        }
        foreach ($column as $key => $c) {
            if (is_array($c)) {
                $relation = $c;
                $query->orWhereHas($c[0], function ($q) use ($relation, $s) {
                    foreach ($relation[1] as $key => $column) {
                        if ($column === 'full_name') {
                            $column = DB::raw('CONCAT(first_name," ",last_name)');

                            $q->where($column, 'LIKE', '%'.$s.'%');
                        } else {
                            if ($key) {
                                $q->orWhere($column, 'LIKE', '%'.$s.'%');
                            } else {
                                $q->where($column, 'LIKE', '%'.$s.'%');
                            }
                        }
                    }
                });
            } else {
                if ($key) {
                    $method = 'orWhere';
                } else {
                    $method = 'where';
                }

                if ($c === 'full_name') {
                    $c = DB::raw('CONCAT(first_name," ",last_name)');
                }

                $query->$method($c, 'LIKE', '%'.$s.'%');
            }
        }

        return $query;
    }

    public function scopeActive($q, $s)
    {
        if ($s == '1' || $s == '0') {
            $q->where('active', $s);
        }

        return $q;
    }

    public function scopeSort($q, $orderBy, $orderDirection)
    {
        return $q->orderBy($orderBy, $orderDirection);
    }

    public function scopeFindByArgs($q, $args): void
    {
        if (is_array($args) && count($args)) {
            foreach ($args as $column => $value) {
                $q->where($column, $value);
            }
        }
    }


}
