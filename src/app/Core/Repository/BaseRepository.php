<?php

declare(strict_types=1);

namespace App\Core\Repository;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
abstract class BaseRepository
{
    /**
     * @var TModel
     */
    protected mixed $model;

    abstract protected function getModel(): string;

    public function __construct()
    {
        $this->model = app($this->getModel());
    }

    /**
     * @return TModel|null
     */
    public function findById(int $modelId): ?Model
    {
        return $this->query()->find($modelId);
    }

    public function create(array $attributes): ?Model
    {
        return $this->query()->create($attributes);
    }

    /**
     * @return TModel|null
     */
    public function update(int $modelId, array $attributes): ?Model
    {
        $model = $this->findById($modelId);

        if ($model === null) {
            return null;
        }

        $model->update($attributes);

        return $model;
    }

    /**
     * @return Collection<int, TModel>
     */
    public function getAll(
        array $columns = ['*'],
        array $relations = []
    ): Collection {
        /** @phpstan-ignore return.type */
        return $this->query()
            ->with($relations)
            ->get($columns);
    }

    final public function deleteById(int $modelId): bool
    {
        $model = $this->findById($modelId);

        if ($model === null) {
            return false;
        }

        return $model->delete();
    }

    /**
     * @return Builder<TModel>
     */
    protected function query(): Builder
    {
        /** @phpstan-var Builder<TModel> $query */
        $query = $this->model->newQuery();

        return $query;
    }
}
