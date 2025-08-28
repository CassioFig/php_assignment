<?php
namespace Repositories;

interface RepositoryInterface
{
    /**
     * Create a new entity.
     *
     * @param object $entity
     * @return object The created entity
     */
    public function create(object $entity): object;

    /**
     * Find an entity by its ID.
     *
     * @param int|string $id
     * @return object|null
     */
    public function findById(int|string $id): ?object;

    /**
     * Find entities by given criteria.
     *
     * @param array $criteria ['column_name' => 'value']
     * @return object[] Array of entities matching the criteria
     */
    public function findBy(array $criteria): array;

    /**
     * Update an existing entity.
     *
     * @param object $entity
     * @return object The updated entity
     */
    public function update(object $entity): object;

    /**
     * Delete an entity.
     *
     * @param object $entity
     * @return bool True on success, false otherwise
     */
    public function delete(object $entity): bool;
}
