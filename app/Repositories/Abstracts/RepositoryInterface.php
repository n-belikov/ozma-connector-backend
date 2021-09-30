<?php

namespace App\Repositories\Abstracts;

use Illuminate\Support\Collection;

/**
 * Interface RepositoryInterface
 * @package App\Repositories\Abstracts
 * @template T
 */
interface RepositoryInterface
{
    /**
     * @return Collection<T>|T[]
     */
    public function all(): Collection;

    /**
     * @param array $relations
     * @return RepositoryInterface
     */
    public function with(array $relations): RepositoryInterface;

    /**
     * @param array $attributes
     * @return T
     */
    public function create(array $attributes);

    /**
     * @param array $attributes
     * @param int|string $id
     * @return T
     */
    public function update(array $attributes, $id);

    /**
     * @param array $attributes
     * @param array $values
     * @return T
     */
    public function updateOrCreate(array $attributes, array $values = []);

    /**
     * @param array $where
     * @param array|string[] $columns
     * @return Collection<T>|T[]
     */
    public function findWhere(array $where, array $columns = ["*"]): Collection;

    /**
     * @param int|string $id
     * @param array|string[] $columns
     * @return T
     */
    public function find($id, array $columns = ['*']);
}