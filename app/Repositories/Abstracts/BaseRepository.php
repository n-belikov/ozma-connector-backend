<?php

namespace App\Repositories\Abstracts;

use Illuminate\Container\Container as Application;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class BaseRepository
 * @package App\Repositories\Abstracts
 * @template T
 */
abstract class BaseRepository implements RepositoryInterface
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->resetModel();
    }

    /**
     * @return string
     */
    protected abstract function model(): string;

    /**
     * @var T
     */
    protected $model;

    /**
     * @return T
     */
    protected function makeModel(): Model
    {
        return $this->model = $this->app->make($this->model());
    }

    protected function resetModel(): void
    {
        $this->makeModel();
    }


    /**
     * @param array $conditions
     */
    protected function applyConditions(array $conditions): void
    {
        foreach ($conditions as $field => $value) {
            if (is_array($value)) {
                list($field, $condition, $val) = $value;
                if ($condition === "in") {
                    $this->model = $this->model->whereIn($field, $val);
                } else {
                    $this->model = $this->model->where($field, $condition, $val);
                }
            } else {
                $this->model = $this->model->where($field, '=', $value);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function create(array $attributes)
    {
        $model = $this->model->newInstance($attributes);
        $model->save();
        $this->resetModel();

        return $model;
    }

    /**
     * @inheritDoc
     */
    public function update(array $attributes, $id)
    {
        $model = $this->model->findOrFail($id);
        $model->fill($attributes);
        $model->save();
        $this->resetModel();

        return $model;
    }

    /**
     * @inheritDoc
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        $model = $this->model->updateOrCreate($attributes, $values);
        $this->resetModel();
        return $model;
    }

    /**
     * @inheritDoc
     */
    public function with(array $relations): RepositoryInterface
    {
        $this->model = $this->model->with($relations);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function findWhere(array $where, array $columns = ["*"]): Collection
    {
        $this->applyConditions($where);
        $result = $this->model->get($columns);
        $this->resetModel();

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function find($id, array $columns = ['*'])
    {
        $result = $this->model->findOrFail($id, $columns);
        $this->resetModel();
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function all(array $columns = ['*']): Collection
    {
        $result = $this->model instanceof Builder ? $this->model->get($columns) : $this->model->all($columns);
        $this->resetModel();
        return $result;
    }
}