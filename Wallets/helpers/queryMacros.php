<?php

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;

Builder::macro('withSumQuery', function ($columns) {
    if (empty($columns)) {
        return $this;
    }

    if (is_null($this->query->columns)) {
        $this->query->select([$this->query->from . '.*']);
    }

    $columns = is_array($columns) ? $columns : func_get_args();
    $columnAndConstraints = [];

    foreach ($columns as $name => $constraints) {

        if (is_numeric($name)) {
            $name = $constraints;
            $constraints = static function () {
            };
        }

        $columnAndConstraints[$name] = $constraints;
    }

    foreach ($columnAndConstraints as $name => $constraints) {
        $segments = explode(' ', $name);

        unset($alias);

        if (count($segments) === 3 && Str::lower($segments[1]) === 'as') {
            [$name, $alias] = [$segments[0], $segments[2]];
        }

// Here we'll extract the relation name and the actual column name that's need to sum.
        $segments = explode('.', $name);

        $relationName = $segments[0];
        $column = $segments[1];

        $relation = $this->getRelationWithoutConstraints($relationName);

        $query = $relation->getRelationExistenceQuery(
            $relation->getRelated()->newQuery(),
            $this,
            new Expression("sum(`$column`)")
        )->setBindings([], 'select');

        $query->callScope($constraints);

        $query = $query->mergeConstraintsFrom($relation->getQuery())->toBase();

        if (count($query->columns) > 1) {
            $query->columns = [$query->columns[0]];
        }

// Finally we will add the proper result column alias to the query and run the subselect
// statement against the query builder. Then we will return the builder instance back
// to the developer for further constraint chaining that needs to take place on it.
        $column = $alias ?? Str::snake(Str::replaceFirst('.', ' ', $name . '_sum'));

        $this->selectSub($query, $column);
    }

    return $this;
});
