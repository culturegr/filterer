<?php

namespace CultureGr\Filterer;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class SortsBuilder
{
    protected Builder $builder;
    protected Model $model;

    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
        $this->model = $builder->getModel();
    }

    public function apply(array $orders): Builder
    {
        foreach ($orders as $order) {
            $this->applyOrderToBuilder($order);
        }

        return $this->builder;
    }

    protected function applyOrderToBuilder(array $order): void
    {
        if ('' === $order['column'] || '' === $order['direction']) {
            return;
        }

        if (false !== strpos($order['column'], '.')) {
            [$relation, $order['column']] = explode('.', $order['column']);
            $this->{Str::camel(class_basename($this->model->$relation()))}($relation, $order);
        } else {
            $this->builder->orderBy($order['column'], $order['direction']);
        }
    }

    protected function belongsTo(string $relation, array $order): void
    {
        $childTable = $this->model->getTable();
        $parentTable = $this->model->$relation()->getRelated()->getTable();
        $parentDatabase = $this->model->$relation()->getRelated()->getConnection()->getDatabaseName();

        // We rely on Laravel naming convensions for defining both primary and foreign keys in the one-to-many relationships.
        $primaryKey = 'id';
        $foreignKey = Str::snake($relation).'_'.$primaryKey;

        $this->builder->orderBy(function ($query) use (
            $order,
            $childTable,
            $parentDatabase,
            $parentTable,
            $primaryKey,
            $foreignKey
        ) {
            $query->select($parentTable.'.'.$order['column'])
                ->from($parentDatabase === ':memory:' ? $parentTable : $parentDatabase.'.'.$parentTable)
                ->whereColumn($childTable.'.'.$foreignKey, $parentTable.'.'.$primaryKey);
        }, $order['direction']);
    }

    protected function hasMany(string $relation, array $order): void
    {
        $parentTable = $this->model->getTable();
        $childTable = $this->model->$relation()->getRelated()->getTable();
        $childDatabase = $this->model->$relation()->getRelated()->getConnection()->getDatabaseName();

        // We rely on Laravel naming convensions for defining both primary and foreign keys in the one-to-many relationships.
        $primaryKey = 'id';
        $foreignKey = Str::singular($this->model->getTable()).'_id';

        $this->builder->orderBy(function ($query) use (
            $order,
            $parentTable,
            $childDatabase,
            $childTable,
            $primaryKey,
            $foreignKey
        ) {
            $query->select($childTable.'.'.$order['column'])
                ->from($childDatabase === ':memory:' ? $childTable : $childDatabase.'.'.$childTable)
                ->whereColumn($childTable.'.'.$foreignKey, $parentTable.'.'.$primaryKey)
                ->orderBy($childTable.'.'.$order['column'], $order['direction'])
                ->limit(1);
        }, $order['direction']);
    }

    protected function belongsToMany(string $relation, array $order): void
    {
        $table = $this->model->getTable();
        $relatedTable = $this->model->$relation()->getRelated()->getTable();
        $relatedDatabase = $this->model->$relation()->getRelated()->getConnection()->getDatabaseName();

        // We rely on Laravel naming convensions for defining pivot name and foreign keys in the many-to-many relationships..
        $pivotTable = collect([Str::singular($relatedTable), Str::singular($table)])->sort()->implode('_');
        $primaryKey = 'id';
        $foreignKey = Str::singular($table).'_id';
        $relatedPrimaryKey = 'id';
        $relatedForeignKey = Str::singular($relatedTable).'_id';

        $this->builder->orderBy(function ($query) use (
            $order,
            $pivotTable, // we assume that the pivot tabl exists on the same database as the original model
            $relatedTable,
            $relatedDatabase,
            $table,
            $primaryKey,
            $foreignKey,
            $relatedPrimaryKey,
            $relatedForeignKey
        ) {
            $query->select($relatedTable.'.'.$order['column'])
                ->from($relatedDatabase === ':memory:' ? $relatedTable : $relatedDatabase.'.'.$relatedTable)
                ->join($pivotTable, $pivotTable.'.'.$relatedForeignKey, '=',
                    $relatedDatabase === ':memory:' ? $relatedTable.'.'.$relatedPrimaryKey : $relatedDatabase.'.'.$relatedTable.'.'.$relatedPrimaryKey)
                ->whereColumn($pivotTable.'.'.$foreignKey, $table.'.'.$primaryKey)
                ->orderBy($relatedTable.'.'.$order['column'], $order['direction'])
                ->limit(1);
        }, $order['direction']);
    }
}
