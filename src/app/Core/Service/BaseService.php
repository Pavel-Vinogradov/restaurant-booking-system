<?php

namespace App\Core\Service;

use App\Core\DTO\BaseDTO;
use Illuminate\Support\Facades\DB;
use Throwable;

abstract class BaseService
{
    abstract protected function getRepository();

    public function getAll(array $relations = [])
    {
        return $this->getRepository()->getAll(relations: $relations);
    }

    public function findById(int $id)
    {
        return $this->getRepository()->findById($id);
    }

    /**
     * @param BaseDTO|array $data
     * @return mixed
     * @throws Throwable
     */
    public function create(BaseDTO|array $data)
    {
        $dataArray = $data instanceof BaseDTO ? $data->toArray() : $data;

        return DB::transaction(function () use ($dataArray) {
            return $this->getRepository()->create($dataArray);
        });
    }

    /**
     * @param int $id
     * @param BaseDTO|array $data
     * @return mixed
     * @throws Throwable
     */
    public function update(int $id, BaseDTO|array $data)
    {
        $dataArray = $data instanceof BaseDTO ? $data->toArray() : $data;

        return DB::transaction(function () use ($id, $dataArray) {
            return $this->getRepository()->update($id, $dataArray);
        });
    }

    public function delete(int $id): bool
    {
        try {
            return DB::transaction(function () use ($id) {
                return $this->getRepository()->deleteById($id);
            });
        } catch (Throwable $e) {
            return false;
        }
    }
}
