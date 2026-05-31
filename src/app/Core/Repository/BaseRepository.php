<?php

declare(strict_types=1);

namespace App\Core\Repository;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @template TModel of Model
 */
abstract class BaseRepository
{
    /**
     * @var TModel
     */
    protected Model $model;

    abstract protected function getModel(): string;

    public function __construct()
    {
        $this->model = app($this->getModel());
    }

    /**
     * @return TModel|null
     */
    final public function findById(int $modelId): ?Model
    {
        return $this->query()->find($modelId);
    }

    final public function create(array $attributes): ?Model
    {
        return $this->query()->create($attributes);
    }

    /**
     * @return TModel|null
     */
    final public function update(int $modelId, array $attributes): ?Model
    {
        $model = $this->findById($modelId);

        if (! $model) {
            return null;
        }

        $model->update($attributes);

        return $model;
    }

    /**
     * @return Collection<int, TModel>
     */
    final public function getAll(
        array $columns = ['*'],
        array $relations = []
    ): Collection {
        return $this->query()
            ->with($relations)
            ->get($columns);
    }

    final public function deleteById(int $modelId): bool
    {
        $model = $this->findById($modelId);

        if (! $model) {
            return false;
        }

        return $model->delete();
    }

    /**
     * @return Builder<TModel>
     */
    protected function query(): Builder
    {
        return $this->model->newQuery();
    }
}
